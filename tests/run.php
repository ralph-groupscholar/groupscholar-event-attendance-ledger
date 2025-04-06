<?php

declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Migrations.php';
require __DIR__ . '/../src/Repositories/EventRepository.php';
require __DIR__ . '/../src/Repositories/AttendanceRepository.php';
require __DIR__ . '/../src/Reports/SummaryReport.php';
require __DIR__ . '/../src/Reports/FollowUpReport.php';

function assertTrue(bool $condition, string $message): void
{
    if (!$condition) {
        echo "FAIL: {$message}\n";
        exit(1);
    }
}

$config = [
    'driver' => 'sqlite',
    'sqlite_path' => ':memory:',
    'host' => '',
    'port' => '',
    'name' => '',
    'user' => '',
    'password' => '',
    'sslmode' => '',
];

$pdo = Database::connect($config);
Migrations::createTables($pdo, 'sqlite');

$eventRepo = new EventRepository($pdo);
$attendanceRepo = new AttendanceRepository($pdo);
$summaryReport = new SummaryReport($pdo);
$followUpReport = new FollowUpReport($pdo);

$eventId = $eventRepo->create('Test Event', '2026-02-01', 'Lab', 'Testing');
assertTrue($eventId > 0, 'Event created');

$attendanceRepo->create($eventId, 'Scholar One', 'one@example.org', 'attended', 4, true, 'Follow-up requested');
$attendanceRepo->create($eventId, 'Scholar Two', 'two@example.org', 'no-show', null, false, 'Missed');

$events = $eventRepo->listAll();
assertTrue(count($events) === 1, 'Event list contains one event');

$records = $attendanceRepo->listByEvent($eventId);
assertTrue(count($records) === 2, 'Attendance list contains two records');

$summary = $summaryReport->attendanceSummary('2026-01-01', '2026-12-31');
assertTrue(count($summary) === 2, 'Summary has two status rows');

$followUps = $followUpReport->listFollowUps('2026-01-01', '2026-12-31');
assertTrue(count($followUps) === 1, 'Follow-up report has one row');

$leaderboard = $summaryReport->engagementLeaderboard('2026-01-01', '2026-12-31', 5);
assertTrue(count($leaderboard) === 1, 'Leaderboard has one event');
assertTrue((int) $leaderboard[0]['attendance_count'] === 2, 'Leaderboard attendance count is correct');

echo "All tests passed.\n";
