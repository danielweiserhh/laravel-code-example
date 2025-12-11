<?php

declare(strict_types=1);

namespace App\Domain\Inbox\Services;

use App\Domain\Exceptions\InvalidContentException;
use App\Domain\Inbox\ValueObjects\InboxItemData;

final class InboxDomainService
{
    public function validateContent(string $content): void
    {
        $trimmed = trim($content);

        if (empty($trimmed)) {
            throw new InvalidContentException(
                'Содержимое заметки не может быть пустым',
                ['content_length' => mb_strlen($content)]
            );
        }

        if (mb_strlen($content) > 10000) {
            throw new InvalidContentException(
                'Содержимое заметки слишком длинное (максимум 10000 символов)',
                ['content_length' => mb_strlen($content), 'max_length' => 10000]
            );
        }
    }

    public function canBeParsedWithAI(InboxItemData $item): bool
    {
        return ! $item->isProcessed && ! empty(trim($item->content));
    }

    public function canBeArchived(InboxItemData $item): bool
    {
        return $item->isProcessed;
    }

    public function looksLikeTask(string $content): bool
    {
        $content = mb_strtolower(trim($content));

        $taskIndicators = [
            'сделать', 'нужно', 'надо', 'задача', 'задачу', 'задачи',
            'купить', 'позвонить', 'написать', 'встретиться',
            'выполнить', 'завершить', 'подготовить', 'проверить',
            'todo', 'task', 'do', 'need', 'must', 'should',
            'buy', 'call', 'write', 'meet', 'complete', 'finish',
        ];

        foreach ($taskIndicators as $indicator) {
            if (mb_strpos($content, $indicator) !== false) {
                return true;
            }
        }

        return false;
    }

    public function extractTaskTitle(string $content): string
    {
        $lines = explode("\n", trim($content));
        $firstLine = trim($lines[0]);

        if (mb_strlen($firstLine) <= 100) {
            return $firstLine;
        }

        return mb_substr($firstLine, 0, 97).'...';
    }
}
