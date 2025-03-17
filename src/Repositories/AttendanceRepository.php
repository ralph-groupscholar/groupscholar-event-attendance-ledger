<?php

declare(strict_types=1);

final class AttendanceRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(
        int $eventId,
        string $name,
        ?string $email,
        string $status,
        ?int $score,
        bool $followUp,
        ?string $notes
    ): int {
        $stmt = $this->pdo->prepare(
            'INSERT INTO gs_event_attendance (
                event_id,
                scholar_name,
                scholar_email,
                status,
                engagement_score,
                follow_up_needed,
                notes
            ) VALUES (
                :event_id,
                :scholar_name,
                :scholar_email,
                :status,
                :engagement_score,
                :follow_up_needed,
                :notes
            )'
        );

        $stmt->execute([
            ':event_id' => $eventId,
            ':scholar_name' => $name,
            ':scholar_email' => $email,
            ':status' => $status,
            ':engagement_score' => $score,
            ':follow_up_needed' => $followUp ? 1 : 0,
            ':notes' => $notes,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listByEvent(int $eventId): array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, scholar_name, scholar_email, status, engagement_score, follow_up_needed, notes, created_at
             FROM gs_event_attendance
             WHERE event_id = :event_id
             ORDER BY id DESC'
        );
        $stmt->execute([':event_id' => $eventId]);

        return $stmt->fetchAll();
    }

    public function seedSample(array $eventIds): void
    {
        if (!$eventIds) {
            return;
        }

        $samples = [
            [$eventIds[0], 'Avery Jordan', 'avery.jordan@example.org', 'attended', 5, true, 'Requested coaching follow-up'],
            [$eventIds[0], 'Leila Patel', 'leila.patel@example.org', 'attended', 4, false, 'Strong engagement'],
            [$eventIds[1], 'Dante Rivera', 'dante.rivera@example.org', 'no-show', null, true, 'Needs reschedule'],
            [$eventIds[1], 'Maya Lin', 'maya.lin@example.org', 'attended', 5, false, 'Submitted FAFSA with staff'],
            [$eventIds[2], 'Noah Brooks', 'noah.brooks@example.org', 'cancelled', null, false, 'Family conflict'],
            [$eventIds[2], 'Rina Sharma', 'rina.sharma@example.org', 'attended', 3, true, 'Asked about mentorship options'],
        ];

        foreach ($samples as $sample) {
            $this->create(
                $sample[0],
                $sample[1],
                $sample[2],
                $sample[3],
                $sample[4],
                $sample[5],
                $sample[6]
            );
        }
    }
}
