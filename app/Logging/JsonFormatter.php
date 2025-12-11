<?php

declare(strict_types=1);

namespace App\Logging;

use Monolog\Formatter\JsonFormatter as BaseJsonFormatter;
use Monolog\LogRecord;

class JsonFormatter extends BaseJsonFormatter
{
    public function format(LogRecord $record): string
    {
        $data = [
            'timestamp' => $record->datetime->format('Y-m-d\TH:i:s.u\Z'),
            'level' => $record->level->getName(),
            'message' => $record->message,
            'service' => env('APP_NAME', 'kanban-api'),
        ];

        if (! empty($record->context)) {
            $data['context'] = $this->normalizeData($record->context);
        }

        if (! empty($record->extra)) {
            $data['extra'] = $this->normalizeData($record->extra);
        }

        $httpContext = array_merge($record->extra, $record->context);

        if (isset($httpContext['request_id'])) {
            $data['request_id'] = $httpContext['request_id'];
        }

        if (isset($httpContext['method'])) {
            $data['method'] = $httpContext['method'];
        }

        if (isset($httpContext['path'])) {
            $data['path'] = $httpContext['path'];
        }

        if (isset($httpContext['ip'])) {
            $data['ip'] = $httpContext['ip'];
        }
        
        if (isset($httpContext['user_id'])) {
            $data['user_id'] = $httpContext['user_id'];
        }

        return json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR)."\n";
    }

    private function normalizeData(mixed $data): mixed
    {
        if (is_array($data)) {
            return array_map([$this, 'normalizeData'], $this->sanitizeSensitiveData($data));
        }

        if (is_object($data)) {
            if ($data instanceof \Throwable) {
                return [
                    'exception' => get_class($data),
                    'message' => $data->getMessage(),
                    'file' => $data->getFile(),
                    'line' => $data->getLine(),
                ];
            }

            if (method_exists($data, 'toArray')) {
                return $this->normalizeData($data->toArray());
            }

            if (method_exists($data, '__toString')) {
                return (string) $data;
            }

            return get_class($data);
        }

        return $data;
    }

    private function sanitizeSensitiveData(array $data): array
    {
        $sensitiveKeys = [
            'password',
            'password_confirmation',
            'token',
            'access_token',
            'refresh_token',
            'api_key',
            'secret',
            'authorization',
            'x-api-key',
            'bearer',
        ];

        foreach ($sensitiveKeys as $key) {
            if (isset($data[$key])) {
                $data[$key] = '[REDACTED]';
            }
        }

        return $data;
    }
}
