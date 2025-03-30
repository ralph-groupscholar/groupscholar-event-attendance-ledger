# Group Scholar Event Attendance Ledger

CLI for logging event attendance, engagement scores, and follow-up needs for Group Scholar programs. It keeps an audit trail of events, supports quick summaries, and uses PostgreSQL for persistent reporting.

## Features
- Capture event details (date, location, notes)
- Log attendance with engagement scores and follow-up flags
- List event rosters per event
- Generate summary stats over a time window
- Produce follow-up queues for outreach
- PostgreSQL-backed persistence with seed data

## Tech
- PHP 8.5
- PostgreSQL (production)
- SQLite (tests only)

## Setup

Create an environment file (do not commit credentials):

```
GS_DB_DRIVER=pgsql
GS_DB_HOST=db-acupinir.groupscholar.com
GS_DB_PORT=23947
GS_DB_NAME=postgres
GS_DB_USER=ralph
GS_DB_PASSWORD=your_password
GS_DB_SSLMODE=prefer
```

Initialize tables and seed data:

```
php bin/gs-event-ledger.php init-db
php bin/gs-event-ledger.php seed
```

## Usage

```
php bin/gs-event-ledger.php add-event --name "Campus Scholars Welcome" --date 2026-03-01 --location "Union Hall"
php bin/gs-event-ledger.php add-attendance --event-id 1 --name "Avery Jordan" --status attended --score 5 --follow-up yes
php bin/gs-event-ledger.php list-events
php bin/gs-event-ledger.php list-attendance --event-id 1
php bin/gs-event-ledger.php summary --since 2026-01-01 --until 2026-02-28
php bin/gs-event-ledger.php follow-ups --since 2026-01-01 --until 2026-02-28
```

## Tests

```
php tests/run.php
```

## Notes
- Production data lives in PostgreSQL. Do not commit credentials.
- If SSL is not supported by the server, set `GS_DB_SSLMODE=disable`.
- SQLite is used only for local tests.
