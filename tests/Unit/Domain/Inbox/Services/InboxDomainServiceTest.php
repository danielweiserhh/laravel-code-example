<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Inbox\Services;

use App\Domain\Inbox\Services\InboxDomainService;
use App\Domain\Inbox\ValueObjects\InboxItemData;
use DomainException;
use Tests\TestCase;

final class InboxDomainServiceTest extends TestCase
{
    private InboxDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InboxDomainService;
    }

    public function test_validate_content_throws_for_empty_content(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Содержимое заметки не может быть пустым');

        $this->service->validateContent('');
    }

    public function test_validate_content_throws_for_whitespace_only(): void
    {
        $this->expectException(DomainException::class);

        $this->service->validateContent('   ');
    }

    public function test_validate_content_throws_for_too_long_content(): void
    {
        $this->expectException(DomainException::class);
        $this->expectExceptionMessage('Содержимое заметки слишком длинное');

        $longContent = str_repeat('a', 10001);
        $this->service->validateContent($longContent);
    }

    public function test_validate_content_accepts_valid_content(): void
    {
        $this->service->validateContent('Valid content');
    }

    public function test_looks_like_task_detects_russian_task_keywords(): void
    {
        $this->assertTrue($this->service->looksLikeTask('Сделать задачу'));
        $this->assertTrue($this->service->looksLikeTask('Нужно купить молоко'));
        $this->assertTrue($this->service->looksLikeTask('Надо позвонить маме'));
    }

    public function test_looks_like_task_detects_english_task_keywords(): void
    {
        $this->assertTrue($this->service->looksLikeTask('TODO: fix bug'));
        $this->assertTrue($this->service->looksLikeTask('Need to call John'));
        $this->assertTrue($this->service->looksLikeTask('Must complete report'));
    }

    public function test_looks_like_task_returns_false_for_non_task_content(): void
    {
        $this->assertFalse($this->service->looksLikeTask('Просто заметка'));
        $this->assertFalse($this->service->looksLikeTask('Запись в дневнике'));
        $this->assertFalse($this->service->looksLikeTask('Вчера был хороший день'));
    }

    public function test_extract_task_title_takes_first_line(): void
    {
        $content = "First line\nSecond line\nThird line";
        $title = $this->service->extractTaskTitle($content);

        $this->assertEquals('First line', $title);
    }

    public function test_extract_task_title_truncates_long_lines(): void
    {
        $longLine = str_repeat('a', 150);
        $title = $this->service->extractTaskTitle($longLine);

        $this->assertLessThanOrEqual(100, mb_strlen($title));
        $this->assertStringEndsWith('...', $title);
    }

    public function test_can_be_parsed_with_ai_returns_true_for_unprocessed_item(): void
    {
        $item = new InboxItemData(
            id: 1,
            content: 'Test content',
            isProcessed: false
        );

        $this->assertTrue($this->service->canBeParsedWithAI($item));
    }

    public function test_can_be_parsed_with_ai_returns_false_for_processed_item(): void
    {
        $item = new InboxItemData(
            id: 1,
            content: 'Test content',
            isProcessed: true
        );

        $this->assertFalse($this->service->canBeParsedWithAI($item));
    }
}
