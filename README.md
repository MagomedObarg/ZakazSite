# Procurement Insight Analyzer

A lightweight PHP application that orchestrates procurement document parsing, AI-driven insight generation, and HTTP endpoints for both web and API consumers. The codebase is intentionally framework-free so that automated tests can exercise the core orchestration logic without external dependencies.

## Features

- **Procurement extraction** — `ProcurementParser` converts structured HTML notices into normalized arrays.
- **AI integration** — `DeepSeekService` prepares and submits prompts to the DeepSeek API (fully faked in tests).
- **Caching & persistence** — `CacheService` handles in-memory TTL caching while results are persisted to SQLite.
- **HTTP endpoints** — Custom router exposes web (`/`, `/analyze`, `/result/{id}`) and API (`/api/analyze`) entry points.
- **Extensive tests** — Pest/PHPUnit suites cover services, orchestration workflows, and HTTP routes with deterministic fakes.

## Getting Started

```bash
# Install dependencies
composer install

# Run the full automated test suite
php artisan test
```

The default configuration uses SQLite stored at `database/database.sqlite`. During automated tests (`APP_ENV=testing`) the DSN is switched to `sqlite::memory:` for a clean, isolated database connection.

### Environment

Copy the provided example configuration and update the DeepSeek credentials if you wish to experiment with real requests.

```bash
cp .env.example .env
```

Key environment variables:

- `DEEPSEEK_ENDPOINT` — Base URL for the DeepSeek API (defaults to staging URL used in tests).
- `DEEPSEEK_API_KEY` — Secret token; leave blank to rely on fakes.
- `DEEPSEEK_MODEL` — Model identifier; defaults to `deepseek-chat`.
- `DB_DSN` — Override the SQLite DSN (e.g. `sqlite:/absolute/path/to/file.sqlite`).

## Local Development Workflow

1. **Seed a sample procurement notice** — Use the HTML template in `resources/examples/procurement-sample.html` as a starting point when crafting new fixtures.
2. **Mock external calls** — All automated tests rely on `App\Support\Http::fake()` to stub DeepSeek and procurement fetches. Follow the same pattern for your own sandbox experiments to avoid accidental outbound traffic.
3. **Regenerate analysis records** — Use the in-memory artisan command `php artisan test` which refreshes the SQLite schema for each run. For manual smoke tests, delete `database/database.sqlite` to reset persisted analyses.

## Testing Strategy

- **Unit tests** validate parser accuracy, caching semantics, DeepSeek request construction, and analysis workflows.
- **Feature tests** exercise the custom router for both web flows (redirects, session flashes, view payloads) and JSON API responses (validation, caching, and error handling).
- **Snapshots** — deterministic JSON fixtures ensure that AI responses remain stable across runs without contacting external services.

Run the entire suite frequently (`php artisan test`) to guarantee regressions are caught early.
