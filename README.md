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

## Error Handling & Troubleshooting

### Error Types and HTTP Status Codes

The application uses centralized exception handling with specific error types:

- **404 (Not Found)** — `ProcurementNotFoundException`: The procurement URL could not be accessed or the resource does not exist.
- **422 (Unprocessable Entity)** — `ProcurementParseException`: The procurement document format is invalid or cannot be parsed.
- **502 (Bad Gateway)** — `DeepSeekException`: The AI analysis service is unavailable or returned an error.
- **500 (Internal Server Error)** — Generic fallback for unexpected errors.

### Error Caching

To prevent repeated failed requests to external services, error states are cached for **1 hour**. When an error occurs during procurement fetching or AI analysis:

1. The error details are stored in the cache with the same key as a successful analysis would use.
2. Subsequent requests for the same URL will retrieve the cached error immediately without re-hitting external services.
3. The cached error includes the status code, error code, user-friendly message, and timestamp.

**To clear cached errors manually:**

```php
// Via the analysis service flush method
$app->analysis()->flush();
```

Or delete `database/database.sqlite` to reset both cached data and persisted analysis records.

### Logs

Application logs are written to `storage/logs/app.log` and include contextual information for debugging:

- **Request ID** — Unique identifier for each request (format: `req_<timestamp><random>`)
- **Route** — The endpoint that handled the request
- **URL** — The procurement URL being analyzed
- **Exception type** — Full class name of the thrown exception
- **Message** — Error message and stack trace details

**Log levels:**
- `ERROR` — Exceptions and failures
- `INFO` — Cached error retrievals and successful operations
- `WARNING` — Potential issues that don't prevent execution

### Common Issues

**Problem:** Analysis fails with "Procurement notice could not be found"  
**Solution:** Verify the URL is accessible and returns a 200 status. Check if the procurement notice is still published.

**Problem:** Repeated "AI analysis service unavailable" errors  
**Solution:** Check `DEEPSEEK_ENDPOINT` and `DEEPSEEK_API_KEY` in your `.env` file. Verify the DeepSeek API is operational. Clear error cache after fixing credentials.

**Problem:** Parse errors on valid-looking procurement documents  
**Solution:** The parser expects specific HTML structure (`<article class="procurement">`). Review `resources/examples/procurement-sample.html` for the expected format.

**Problem:** Errors persist after fixing the underlying issue  
**Solution:** Error responses are cached for 1 hour. Use `$app->analysis()->flush()` to clear the cache immediately.
