<?php

declare(strict_types=1);

namespace Tests\Unit\Logging;

use App\Logging\JsonFormatter;
use Monolog\Level;
use Monolog\LogRecord;
use PHPUnit\Framework\TestCase;

class JsonFormatterTest extends TestCase
{
    private JsonFormatter $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->formatter = new JsonFormatter;
    }

    public function test_formats_basic_log_without_http_context(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'Test message',
            context: ['key' => 'value'],
            extra: []
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertIsString($result);
        $this->assertIsArray($decoded);
        $this->assertStringContainsString('2024-01-01T12:00:00', $decoded['timestamp']);
        $this->assertSame('INFO', $decoded['level']);
        $this->assertSame('Test message', $decoded['message']);
        $this->assertArrayHasKey('context', $decoded);
        $this->assertSame('value', $decoded['context']['key']);
    }

    public function test_formats_log_with_http_context_in_extra(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'HTTP Request',
            context: [],
            extra: [
                'request_id' => 'req_12345',
                'method' => 'GET',
                'path' => '/api/boards',
                'ip' => '127.0.0.1',
                'user_id' => 42,
            ]
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertSame('req_12345', $decoded['request_id']);
        $this->assertSame('GET', $decoded['method']);
        $this->assertSame('/api/boards', $decoded['path']);
        $this->assertSame('127.0.0.1', $decoded['ip']);
        $this->assertSame(42, $decoded['user_id']);
    }

    public function test_handles_exception_in_context(): void
    {
        $exception = new \RuntimeException('Test exception', 500);
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Error,
            message: 'Error occurred',
            context: ['exception' => $exception],
            extra: []
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertIsArray($decoded['context']['exception']);
        $this->assertSame('RuntimeException', $decoded['context']['exception']['exception']);
        $this->assertSame('Test exception', $decoded['context']['exception']['message']);
        $this->assertArrayHasKey('file', $decoded['context']['exception']);
        $this->assertArrayHasKey('line', $decoded['context']['exception']);
    }

    public function test_handles_nested_arrays(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'Nested data',
            context: [
                'nested' => [
                    'level1' => [
                        'level2' => 'value',
                    ],
                ],
            ],
            extra: []
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertSame('value', $decoded['context']['nested']['level1']['level2']);
    }

    public function test_handles_empty_context_and_extra(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Debug,
            message: 'Empty context',
            context: [],
            extra: []
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertSame('Empty context', $decoded['message']);
        $this->assertArrayNotHasKey('context', $decoded);
        $this->assertArrayNotHasKey('extra', $decoded);
    }

    public function test_produces_valid_json(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Warning,
            message: 'Warning message',
            context: ['key' => 'value'],
            extra: []
        );

        $result = $this->formatter->format($record);

        $this->assertJson($result);
        $this->assertStringEndsWith("\n", $result);
    }

    public function test_handles_unicode_characters(): void
    {
        $record = new LogRecord(
            datetime: new \DateTimeImmutable('2024-01-01 12:00:00'),
            channel: 'test',
            level: Level::Info,
            message: 'Тест с кириллицей',
            context: ['emoji' => '🚀'],
            extra: []
        );

        $result = $this->formatter->format($record);
        $decoded = json_decode($result, true);

        $this->assertSame('Тест с кириллицей', $decoded['message']);
        $this->assertSame('🚀', $decoded['context']['emoji']);
    }
}
