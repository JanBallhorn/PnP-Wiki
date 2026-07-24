<?php

namespace App\Repository;

use DateTime;
use Exception;

/**
 * Read-only aggregate queries for the statistics page. These are wiki-wide
 * counts and rankings that deliberately include private articles - none of
 * them name a concrete article, so no private content leaks. Any future stat
 * that names a specific article must filter private articles the viewer may
 * not see.
 */
class StatisticsRepository extends Repository
{
    public function __construct()
    {
        $this->connectDB();
    }

    /**
     * Ranking of users by number of non-empty (non-stub) articles they
     * created. Every user is listed (LEFT JOIN), so users with zero articles
     * still appear - the caller shows up to $limit, sorted by article count
     * descending then username. $currentMonthOnly counts only articles
     * published in the current calendar month (the article conditions live in
     * the JOIN so zero-count users are kept).
     * @return array<int, array{username:string, count:int}>
     */
    public function userRanking(bool $currentMonthOnly, int $limit = 10): array
    {
        $query =
            "SELECT u.username AS username, COUNT(a.id) AS cnt
            FROM users u
            LEFT JOIN articles a ON a.created_by = u.id AND a.empty = 0";
        if($currentMonthOnly){
            $query .= " AND a.published >= ?";
        }
        $query .= " WHERE u.test_user = 0 GROUP BY u.id, u.username ORDER BY cnt DESC, u.username ASC LIMIT ?";
        $stmt = $this->db->prepare($query);
        if($currentMonthOnly){
            $monthStart = date('Y-m-01 00:00:00');
            $stmt->bind_param("si", $monthStart, $limit);
        }
        else{
            $stmt->bind_param("i", $limit);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        $ranking = [];
        while($row = $result->fetch_object()){
            $ranking[] = ['username' => $row->username, 'count' => (int)$row->cnt];
        }
        return $ranking;
    }

    /**
     * Cumulative article count per month since the first article, both total
     * and non-empty-only. Missing months are filled so the time axis is
     * continuous.
     * @return array<int, array{month:string, total:int, nonEmpty:int}>
     * @throws Exception
     */
    public function growthByMonth(): array
    {
        $query =
            "SELECT DATE_FORMAT(a.published, '%Y-%m') AS ym,
                    COUNT(*) AS total,
                    SUM(CASE WHEN a.empty = 0 THEN 1 ELSE 0 END) AS non_empty
            FROM articles a
            INNER JOIN users u ON a.created_by = u.id
            WHERE u.test_user = 0
            GROUP BY ym
            ORDER BY ym ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $perMonth = [];
        while($row = $result->fetch_object()){
            $perMonth[$row->ym] = ['total' => (int)$row->total, 'nonEmpty' => (int)$row->non_empty];
        }
        if(empty($perMonth)){
            return [];
        }
        $months = array_keys($perMonth);
        $cursor = new DateTime($months[0] . '-01');
        $end = new DateTime(date('Y-m') . '-01');
        $growth = [];
        $cumTotal = 0;
        $cumNonEmpty = 0;
        while($cursor <= $end){
            $ym = $cursor->format('Y-m');
            $cumTotal += $perMonth[$ym]['total'] ?? 0;
            $cumNonEmpty += $perMonth[$ym]['nonEmpty'] ?? 0;
            $growth[] = ['month' => $ym, 'total' => $cumTotal, 'nonEmpty' => $cumNonEmpty];
            $cursor->modify('+1 month');
        }
        return $growth;
    }

    /**
     * Number of articles per category, most first.
     * @return array<int, array{name:string, count:int}>
     */
    public function articlesPerCategory(): array
    {
        $query =
            "SELECT c.name AS name, COUNT(ac.article) AS cnt
            FROM article_categories ac
            INNER JOIN categories c ON ac.category = c.id
            INNER JOIN articles a ON ac.article = a.id
            INNER JOIN users u ON a.created_by = u.id
            WHERE u.test_user = 0
            GROUP BY ac.category, c.name
            ORDER BY cnt DESC, c.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while($row = $result->fetch_object()){
            $rows[] = ['name' => $row->name, 'count' => (int)$row->cnt];
        }
        return $rows;
    }

    /**
     * All sources, measured by the number of distinct articles that cite each
     * source, most first. Returns the full list so the donut's "Sonstige"
     * slice can sum the real tail; the view caps how many bars it shows.
     * @return array<int, array{name:string, type:string, count:int}>
     */
    public function mostUsedSources(): array
    {
        $query =
            "SELECT s.name AS name, s.type AS type, COUNT(DISTINCT asrc.article) AS cnt
            FROM article_sources asrc
            INNER JOIN sources s ON asrc.source = s.id
            INNER JOIN articles a ON asrc.article = a.id
            INNER JOIN users u ON a.created_by = u.id
            WHERE u.test_user = 0
            GROUP BY asrc.source, s.name, s.type
            ORDER BY cnt DESC, s.name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->get_result();
        $rows = [];
        while($row = $result->fetch_object()){
            $rows[] = ['name' => $row->name, 'type' => $row->type, 'count' => (int)$row->cnt];
        }
        return $rows;
    }
}
