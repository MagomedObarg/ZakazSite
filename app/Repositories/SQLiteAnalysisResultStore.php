<?php

namespace App\Repositories;

use App\Contracts\AnalysisResultStore;
use App\Data\AnalysisResult;
use PDO;

class SQLiteAnalysisResultStore implements AnalysisResultStore
{
    public function __construct(protected PDO $connection)
    {
        $this->install();
    }

    public function save(AnalysisResult $result): void
    {
        $payload = json_encode($result->toArray());

        $statement = $this->connection->prepare(
            'INSERT INTO analysis_results (id, url, payload, created_at)
             VALUES (:id, :url, :payload, :created_at)
             ON CONFLICT(id) DO UPDATE SET url = excluded.url, payload = excluded.payload, created_at = excluded.created_at'
        );

        $statement->execute([
            'id' => $result->id,
            'url' => $result->url,
            'payload' => $payload,
            'created_at' => $result->generatedAt,
        ]);
    }

    public function find(string $id): ?AnalysisResult
    {
        $statement = $this->connection->prepare('SELECT payload FROM analysis_results WHERE id = :id LIMIT 1');
        $statement->execute(['id' => $id]);

        $record = $statement->fetch(PDO::FETCH_ASSOC);

        if (! $record) {
            return null;
        }

        /** @var array<string, mixed> $decoded */
        $decoded = json_decode($record['payload'], true, flags: JSON_THROW_ON_ERROR);

        return AnalysisResult::fromArray($decoded);
    }

    public function clear(): void
    {
        $this->connection->exec('DELETE FROM analysis_results');
    }

    protected function install(): void
    {
        $this->connection->exec(
            'CREATE TABLE IF NOT EXISTS analysis_results (
                id TEXT PRIMARY KEY,
                url TEXT NOT NULL,
                payload TEXT NOT NULL,
                created_at TEXT NOT NULL
            )'
        );
    }
}
