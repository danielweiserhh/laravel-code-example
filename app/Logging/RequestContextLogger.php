<?php

declare(strict_types=1);

namespace App\Logging;

use Illuminate\Http\Request;
use Illuminate\Log\Logger;
use Monolog\LogRecord;

class RequestContextLogger
{
    public function __invoke(Logger $logger): void
    {
        foreach ($logger->getHandlers() as $handler) {
            
            if (method_exists($handler, 'pushProcessor')) {
                $handler->pushProcessor(function (LogRecord|array $record) {
                    try {
                        $request = app('request');
                    } catch (\Throwable) {
                        return $record;
                    }

                    if ($request instanceof Request) {
                        $requestId = null;
                        try {
                            $requestId = app()->bound('request_id') ? app('request_id') : null;
                        } catch (\Throwable) {
                            
                        }

                        $context = [
                            'request_id' => $requestId ?? $request->header('X-Request-ID'),
                            'user_id' => $request->user()?->id,
                            'route' => $request->route()?->getName() ?? $request->path(),
                            'method' => $request->method(),
                            'ip' => $request->ip(),
                        ];

                        if ($record instanceof LogRecord) {
                            $record->extra = array_merge($record->extra ?? [], $context);
                        } else {
                            if (! isset($record['extra'])) {
                                $record['extra'] = [];
                            }
                            
                            $record['extra'] = array_merge($record['extra'], $context);
                        }
                    }

                    return $record;
                });
            }
        }
    }
}
