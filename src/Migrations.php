<?php

declare(strict_types=1);

final class Migrations
{
    public static function createTables(PDO $pdo, string $driver): void
    {
        if ($driver === 'sqlite') {
            $pdo->exec('CREATE TABLE IF NOT EXISTS gs_event_events (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL,
                event_date TEXT NOT NULL,
                location TEXT,
                notes TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP
            )');

            $pdo->exec('CREATE TABLE IF NOT EXISTS gs_event_attendance (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                event_id INTEGER NOT NULL,
                scholar_name TEXT NOT NULL,
                scholar_email TEXT,
                status TEXT NOT NULL,
                engagement_score INTEGER,
                follow_up_needed INTEGER DEFAULT 0,
                notes TEXT,
                created_at TEXT DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY(event_id) REFERENCES gs_event_events(id) ON DELETE CASCADE
            )');

            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_gs_event_attendance_event_id ON gs_event_attendance(event_id)');
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_gs_event_attendance_status ON gs_event_attendance(status)');
        } else {
            $pdo->exec('CREATE TABLE IF NOT EXISTS gs_event_events (
                id BIGSERIAL PRIMARY KEY,
                name TEXT NOT NULL,
                event_date DATE NOT NULL,
                location TEXT,
                notes TEXT,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )');

            $pdo->exec('CREATE TABLE IF NOT EXISTS gs_event_attendance (
                id BIGSERIAL PRIMARY KEY,
                event_id BIGINT NOT NULL REFERENCES gs_event_events(id) ON DELETE CASCADE,
                scholar_name TEXT NOT NULL,
                scholar_email TEXT,
                status TEXT NOT NULL,
                engagement_score INTEGER,
                follow_up_needed BOOLEAN NOT NULL DEFAULT FALSE,
                notes TEXT,
                created_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
            )');

            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_gs_event_attendance_event_id ON gs_event_attendance(event_id)');
            $pdo->exec('CREATE INDEX IF NOT EXISTS idx_gs_event_attendance_status ON gs_event_attendance(status)');
        }
    }
}
