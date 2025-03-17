<?php

declare(strict_types=1);

final class SummaryReport
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function attendanceSummary(string $since, string $until): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT status,
                    COUNT(*) AS count,
                    AVG(engagement_score) AS avg_score,
                    SUM(CASE WHEN follow_up_needed THEN 1 ELSE 0 END) AS follow_ups
             FROM gs_event_attendance
             WHERE date(created_at) BETWEEN :since AND :until
             GROUP BY status
             ORDER BY count DESC'
        );
        $stmt->execute([
            ':since' => $since,
            ':until' => $until,
        ]);

        return $stmt->fetchAll();
    }
}
