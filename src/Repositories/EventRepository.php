<?php

declare(strict_types=1);

final class EventRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create(string $name, string $date, ?string $location, ?string $notes): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO gs_event_events (name, event_date, location, notes)
             VALUES (:name, :event_date, :location, :notes)'
        );
        $stmt->execute([
            ':name' => $name,
            ':event_date' => $date,
            ':location' => $location,
            ':notes' => $notes,
        ]);

        return (int) $this->pdo->lastInsertId();
    }

    public function listAll(): array
    {
        $stmt = $this->pdo->query(
            'SELECT id, name, event_date, location, notes, created_at
             FROM gs_event_events
             ORDER BY event_date DESC, id DESC'
        );

        return $stmt->fetchAll();
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->pdo->prepare(
            'SELECT id, name, event_date, location, notes, created_at
             FROM gs_event_events
             WHERE id = :id'
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();

        return $row ?: null;
    }

    public function seedSample(): array
    {
        $events = [
            ['Campus Scholars Welcome', '2026-01-24', 'Union Hall', 'Kickoff for spring cohort'],
            ['FAFSA Support Lab', '2026-02-02', 'Room 204', 'Hands-on filing session'],
            ['Mentor Roundtable', '2026-02-06', 'Community Center', 'Career and leadership discussion'],
        ];

        $ids = [];
        foreach ($events as $event) {
            $ids[] = $this->create($event[0], $event[1], $event[2], $event[3]);
        }

        return $ids;
    }
}
