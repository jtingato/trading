# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Monarch is a **trading journal web application** — a server-side rendered PHP app backed by SQLite. There is no build step and no frontend framework.

## Commands

```bash
# Run development server (from project root)
php -S localhost:8000

# Run all tests
phpunit --stop-on-error

# Run a single test file
phpunit tests/path/to/SomeTest.php
```

PHP 8.4.1 is expected (configured via MAMP). Composer manages autoloading only — the single dev dependency is PHPUnit 9.

## Architecture

### Request flow

1. **`index.php`** — entry point. Bootstraps Composer autoload, instantiates `NavigationRouter`, and dispatches based on `?page=` query parameter. Renders a shared HTML shell (header/main/footer) around whatever page PHP file is resolved.

2. **`/app/Routing/NavigationRouter.php`** — file-based router. Searches `/pages/` recursively for the requested filename. Uses `basename()` to prevent directory traversal.

3. **`/Http/RequestHandler.php`** — AJAX endpoint for all client-side mutations (cell edits, column widths, column reordering, dropdown option creation). Accepts and returns JSON. No routing framework — distinguished by `isset()` checks on POST fields.

### Data layer (`/app/Data/`)

- **`DatabaseManager.php`** — base class; opens the SQLite PDO connection to `/app/Data/monarch.db`.
- **`JournalDataManager.php`** — singleton extending `DatabaseManager`; all SQL for the journal lives here (reads, writes, field metadata, dropdown options).
- **`Models/JournalField.php`** — data model for a field's metadata (name, display name, visibility, ordering, width) with a factory method.

### View model layer (`/app/ViewModels/journalViewModel.php`)

`JournalViewModel` sits between `JournalDataManager` and the views. It owns field visibility, ordering, and display name logic and exposes clean arrays to templates.

### View layer (`/pages/`)

Plain PHP templates with inline HTML. Key pages:

- `journal/journal.php` — main journal page shell; wires up modals and actions.
- `journal/journalTable.php` — renders the table with dynamic column handling.
- `journal/journalSelectFields.php` — modal for toggling field visibility/ordering.
- `journal/js/journalTable.js` — all client-side interactivity (SortableJS drag-reorder, `contenteditable` inline editing, Fetch API calls to `RequestHandler.php`).
- `journal/css/journal.css` — journal-specific styles.

### Database (SQLite at `/app/Data/monarch.db`)

Three tables — no migration system exists; schema changes are applied manually:

| Table | Purpose |
|---|---|
| `trading_journal` | One row per trade: symbol, side, quantity, price, broker, strategy, notes, etc. |
| `journal_fields` | Per-user field metadata: ordering, display name, visibility, column width. |
| `journal_dropdown_options` | Valid options for select-type fields (e.g. asset_type, side). |

### Namespacing

PSR-4 autoloading maps `Monarch\` → `/app/`. All classes under `/app/` use `namespace Monarch\…`.

## Key patterns

- **Singleton data manager**: call `JournalDataManager::getInstance()` — do not instantiate directly.
- **AJAX via RequestHandler**: the frontend posts JSON to `Http/RequestHandler.php`; responses are JSON. Add new AJAX operations there with an `isset()` guard.
- **Strict types**: all PHP files use `declare(strict_types=1)`.
- **No ORM**: raw PDO with named parameters throughout.
