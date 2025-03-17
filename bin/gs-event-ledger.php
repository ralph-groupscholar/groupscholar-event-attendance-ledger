<?php

declare(strict_types=1);

require __DIR__ . '/../src/Config.php';
require __DIR__ . '/../src/Database.php';
require __DIR__ . '/../src/Migrations.php';
require __DIR__ . '/../src/Repositories/EventRepository.php';
require __DIR__ . '/../src/Repositories/AttendanceRepository.php';
require __DIR__ . '/../src/Reports/SummaryReport.php';

function usage(): void
{
    $help = <<<TXT
Group Scholar Event Attendance Ledger

Usage:
  php bin/gs-event-ledger.php init-db
  php bin/gs-event-ledger.php seed
  php bin/gs-event-ledger.php add-event --name "Name" --date YYYY-MM-DD [--location ""] [--notes ""]
  php bin/gs-event-ledger.php add-attendance --event-id 1 --name "Scholar" [--email ""] --status attended|no-show|cancelled [--score 1-5] [--follow-up yes|no] [--notes ""]
  php bin/gs-event-ledger.php list-events
  php bin/gs-event-ledger.php list-attendance --event-id 1
  php bin/gs-event-ledger.php summary [--since YYYY-MM-DD] [--until YYYY-MM-DD]

Environment:
  GS_DB_DRIVER=pgsql|sqlite
  GS_DB_HOST, GS_DB_PORT, GS_DB_NAME, GS_DB_USER, GS_DB_PASSWORD, GS_DB_SSLMODE
  GS_SQLITE_PATH (only for sqlite)
TXT;

    echo $help . "\n";
}

function parseArgs(array $argv): array
{
    $args = [];
    for ($i = 2; $i < count($argv); $i++) {
        $arg = $argv[$i];
        if (str_starts_with($arg, '--')) {
            $key = substr($arg, 2);
            $value = true;
            if (isset($argv[$i + 1]) && !str_starts_with($argv[$i + 1], '--')) {
                $value = $argv[$i + 1];
                $i++;
            }
            $args[$key] = $value;
        }
    }

    return $args;
}

function requireValue(array $args, string $key): string
{
    if (!isset($args[$key]) || $args[$key] === true) {
        echo "Missing required flag --{$key}.\n";
        exit(1);
    }

    return (string) $args[$key];
}

function valueToBool(mixed $value): bool
{
    if (is_bool($value)) {
        return $value;
    }

    if (is_int($value)) {
        return $value === 1;
    }

    $normalized = strtolower((string) $value);

    return in_array($normalized, ['1', 'true', 't', 'yes', 'y'], true);
}

$command = $argv[1] ?? 'help';
if ($command === 'help' || $command === '--help' || $command === '-h') {
    usage();
    exit(0);
}

$config = Config::fromEnv();
$pdo = Database::connect($config);

$eventRepo = new EventRepository($pdo);
$attendanceRepo = new AttendanceRepository($pdo);
$summaryReport = new SummaryReport($pdo);

$args = parseArgs($argv);

switch ($command) {
    case 'init-db':
        Migrations::createTables($pdo, $config['driver']);
        echo "Database initialized.\n";
        break;

    case 'seed':
        Migrations::createTables($pdo, $config['driver']);
        $eventIds = $eventRepo->seedSample();
        $attendanceRepo->seedSample($eventIds);
        echo "Seed data inserted.\n";
        break;

    case 'add-event':
        $name = requireValue($args, 'name');
        $date = requireValue($args, 'date');
        $location = $args['location'] ?? null;
        $notes = $args['notes'] ?? null;
        $id = $eventRepo->create($name, $date, $location ? (string) $location : null, $notes ? (string) $notes : null);
        echo "Event created with ID {$id}.\n";
        break;

    case 'add-attendance':
        $eventId = (int) requireValue($args, 'event-id');
        $name = requireValue($args, 'name');
        $status = requireValue($args, 'status');
        $email = $args['email'] ?? null;
        $score = isset($args['score']) ? (int) $args['score'] : null;
        $followUpRaw = $args['follow-up'] ?? 'no';
        $followUp = in_array(strtolower((string) $followUpRaw), ['yes', 'true', '1'], true);
        $notes = $args['notes'] ?? null;
        $id = $attendanceRepo->create(
            $eventId,
            $name,
            $email ? (string) $email : null,
            $status,
            $score,
            $followUp,
            $notes ? (string) $notes : null
        );
        echo "Attendance logged with ID {$id}.\n";
        break;

    case 'list-events':
        $events = $eventRepo->listAll();
        if (!$events) {
            echo "No events found.\n";
            break;
        }
        foreach ($events as $event) {
            printf(
                "[%s] %s | %s | %s\n",
                $event['id'],
                $event['event_date'],
                $event['name'],
                $event['location'] ?? 'Remote'
            );
        }
        break;

    case 'list-attendance':
        $eventId = (int) requireValue($args, 'event-id');
        $records = $attendanceRepo->listByEvent($eventId);
        if (!$records) {
            echo "No attendance records found.\n";
            break;
        }
        foreach ($records as $row) {
            printf(
                "[%s] %s | %s | score:%s | follow-up:%s\n",
                $row['id'],
                $row['scholar_name'],
                $row['status'],
                $row['engagement_score'] ?? 'n/a',
                valueToBool($row['follow_up_needed']) ? 'yes' : 'no'
            );
        }
        break;

    case 'summary':
        $since = $args['since'] ?? date('Y-m-d', strtotime('-30 days'));
        $until = $args['until'] ?? date('Y-m-d');
        $rows = $summaryReport->attendanceSummary((string) $since, (string) $until);
        if (!$rows) {
            echo "No attendance data between {$since} and {$until}.\n";
            break;
        }
        echo "Attendance summary ({$since} to {$until}):\n";
        foreach ($rows as $row) {
            $avgScore = $row['avg_score'] !== null ? number_format((float) $row['avg_score'], 2) : 'n/a';
            printf(
                "- %s: %s records | avg score %s | follow-ups %s\n",
                $row['status'],
                $row['count'],
                $avgScore,
                $row['follow_ups']
            );
        }
        break;

    default:
        echo "Unknown command: {$command}\n";
        usage();
        exit(1);
}
