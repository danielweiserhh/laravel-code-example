<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Inbox\Services;

use App\Domain\Exceptions\InvalidContentException;
use App\Domain\Inbox\Services\InboxDomainService;
use PHPUnit\Framework\TestCase;

final class InboxDomainServiceInvariantsTest extends TestCase
{
    private InboxDomainService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new InboxDomainService();
    }

    public function test_cannot_validate_empty_content(): void
    {
        $this->expectException(InvalidContentException::class);
        $this->expectExceptionMessage('Содержимое заметки не может быть пустым');

        $this->service->validateContent('');
    }

    public function test_cannot_validate_whitespace_only_content(): void
    {
        $this->expectException(InvalidContentException::class);

        $this->service->validateContent('   ');
    }

    public function test_cannot_validate_content_exceeding_max_length(): void
    {
        $longContent = str_repeat('a', 10001);

        $this->expectException(InvalidContentException::class);
        $this->expectExceptionMessage('Содержимое заметки слишком длинное');

        $this->service->validateContent($longContent);
    }

    public function test_can_validate_valid_content(): void
    {
        $this->service->validateContent('Valid content');
        $this->service->validateContent(str_repeat('a', 10000));
    }
}
