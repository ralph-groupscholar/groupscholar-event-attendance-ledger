<?php

declare(strict_types=1);

final class FollowUpReport
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listFollowUps(string $since, string $until): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT a.id,
                    a.scholar_name,
                    a.scholar_email,
                    a.status,
                    a.engagement_score,
                    a.notes,
                    a.created_at,
                    e.name AS event_name,
                    e.event_date
             FROM gs_event_attendance a
             JOIN gs_event_events e ON e.id = a.event_id
             WHERE a.follow_up_needed = TRUE
               AND date(a.created_at) BETWEEN :since AND :until
             ORDER BY e.event_date DESC, a.created_at DESC'
        );
        $stmt->execute([
            ':since' => $since,
            ':until' => $until,
        ]);

        return $stmt->fetchAll();
    }
}
