<?php

namespace App;

use DateTime;
use mysqli;

/**
 * Caps repeated failed logins against the same username/email from the same IP so
 * the login form can't be brute-forced - there was previously no limit on password
 * guesses at all. Self-migrating (CREATE TABLE IF NOT EXISTS) so it needs no manual
 * schema change on deploy, consistent with how the rest of this app has no migration
 * tooling of its own.
 */
class LoginThrottle
{
    private const MAX_ATTEMPTS = 5;
    private const WINDOW_SECONDS = 900;

    private mysqli $db;

    public function __construct()
    {
        $this->db = Database::dbConnect()->getConnection();
        $this->db->query(
            "CREATE TABLE IF NOT EXISTS `login_attempts` (
                `identifier` VARCHAR(255) NOT NULL,
                `ip` VARCHAR(45) NOT NULL,
                `attempted_at` DATETIME NOT NULL,
                INDEX `identifier_ip_attempted_at` (`identifier`, `ip`, `attempted_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
        );
    }

    public function isBlocked(string $identifier): bool
    {
        $ip = $this->clientIp();
        $since = (new DateTime())->modify('-' . self::WINDOW_SECONDS . ' seconds')->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM `login_attempts` WHERE `identifier` = ? AND `ip` = ? AND `attempted_at` > ?");
        $stmt->bind_param("sss", $identifier, $ip, $since);
        $stmt->execute();
        $count = (int)$stmt->get_result()->fetch_row()[0];
        return $count >= self::MAX_ATTEMPTS;
    }

    public function recordFailure(string $identifier): void
    {
        $ip = $this->clientIp();
        $now = (new DateTime())->format('Y-m-d H:i:s');
        $stmt = $this->db->prepare("INSERT INTO `login_attempts` (`identifier`, `ip`, `attempted_at`) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $identifier, $ip, $now);
        $stmt->execute();
    }

    public function clear(string $identifier): void
    {
        $ip = $this->clientIp();
        $stmt = $this->db->prepare("DELETE FROM `login_attempts` WHERE `identifier` = ? AND `ip` = ?");
        $stmt->bind_param("ss", $identifier, $ip);
        $stmt->execute();
    }

    private function clientIp(): string
    {
        return substr((string)($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45);
    }
}
