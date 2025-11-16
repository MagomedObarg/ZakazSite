<?php

namespace App\Exceptions;

class SimpleLogger implements LoggerInterface
{
    protected string $logFile;

    public function __construct(?string $logFile = null)
    {
        $this->logFile = $logFile ?? $this->getDefaultLogFile();
        $this->ensureLogDirectoryExists();
    }

    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }

    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }

    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }

    protected function log(string $level, string $message, array $context = []): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextJson = json_encode($context, JSON_UNESCAPED_SLASHES);
        $logLine = sprintf("[%s] %s: %s | Context: %s\n", $timestamp, $level, $message, $contextJson);

        error_log($logLine, 3, $this->logFile);
    }

    protected function getDefaultLogFile(): string
    {
        $logsDir = dirname(__DIR__, 2) . '/storage/logs';

        return $logsDir . '/app.log';
    }

    protected function ensureLogDirectoryExists(): void
    {
        $dir = dirname($this->logFile);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
}
