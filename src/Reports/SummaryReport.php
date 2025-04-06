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

    public function engagementLeaderboard(string $since, string $until, int $limit): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT e.id,
                    e.name,
                    e.event_date,
                    e.location,
                    COUNT(a.id) AS attendance_count,
                    AVG(a.engagement_score) AS avg_score,
                    SUM(CASE WHEN a.follow_up_needed THEN 1 ELSE 0 END) AS follow_ups,
                    SUM(CASE WHEN a.status = \'attended\' THEN 1 ELSE 0 END) AS attended_count,
                    SUM(CASE WHEN a.status = \'no-show\' THEN 1 ELSE 0 END) AS no_show_count
             FROM gs_event_events e
             LEFT JOIN gs_event_attendance a
               ON a.event_id = e.id
              AND date(a.created_at) BETWEEN :since AND :until
             GROUP BY e.id, e.name, e.event_date, e.location
             ORDER BY (avg_score IS NULL) ASC, avg_score DESC, attendance_count DESC, e.event_date DESC
             LIMIT :limit'
        );
        $stmt->bindValue(':since', $since);
        $stmt->bindValue(':until', $until);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
