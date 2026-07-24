<?php

namespace App\Controller;

use App\Repository\StatisticsRepository;
use Exception;

class StatisticsController extends Controller
{
    public function __construct(
        private readonly StatisticsRepository $statisticsRepository = new StatisticsRepository()
    ){}

    /**
     * @throws Exception
     */
    public function index(): void
    {
        if(!$this->checkLogin()){
            $this->render('statistics.twig');
            return;
        }
        $growth = $this->statisticsRepository->growthByMonth();
        $perCategory = $this->statisticsRepository->articlesPerCategory();
        $sources = $this->statisticsRepository->mostUsedSources();
        $this->render('statistics.twig', [
            'rankingMonth' => $this->statisticsRepository->userRanking(true),
            'rankingTotal' => $this->statisticsRepository->userRanking(false),
            'growth' => $growth,
            'growthAxis' => $this->growthAxis($growth),
            'perCategory' => $perCategory,
            'categoryDonut' => $this->topNWithOther($perCategory, 8),
            'sources' => $sources,
            'sourceDonut' => $this->topNWithOther($sources, 5)
        ]);
    }

    /**
     * Reduces a desc-sorted {name,count} list to the top $topN entries plus a
     * single "Sonstige" entry summing the rest, for the donut charts. Returns
     * the list unchanged when it is already at or below $topN.
     * @param array<int, array{name:string, count:int}> $rows
     * @return array<int, array{name:string, count:int}>
     */
    private function topNWithOther(array $rows, int $topN = 5): array
    {
        if(count($rows) <= $topN){
            return $rows;
        }
        $top = array_slice($rows, 0, $topN);
        $otherCount = 0;
        foreach(array_slice($rows, $topN) as $row){
            $otherCount += $row['count'];
        }
        $top[] = ['name' => 'Sonstige', 'count' => $otherCount, 'other' => true];
        return $top;
    }

    /**
     * Builds a "nice" y-axis for the growth chart: rounds the max cumulative
     * count up to a round number and returns evenly spaced integer ticks, so
     * the axis reads 0/50/100/… instead of raw fractions of the exact max.
     * @param array<int, array{month:string, total:int, nonEmpty:int}> $growth
     * @return array{max:int, ticks:int[]}
     */
    private function growthAxis(array $growth): array
    {
        $maxVal = empty($growth) ? 1 : (int)end($growth)['total'];
        if($maxVal < 1){
            $maxVal = 1;
        }
        $rawStep = $maxVal / 5;
        $mag = 10 ** (int)floor(log10(max($rawStep, 1)));
        $norm = $rawStep / $mag;
        $niceNorm = $norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 5 ? 5 : 10));
        $step = (int)max(1, $niceNorm * $mag);
        $axisMax = (int)(ceil($maxVal / $step) * $step);
        return ['max' => $axisMax, 'ticks' => range(0, $axisMax, $step)];
    }
}
