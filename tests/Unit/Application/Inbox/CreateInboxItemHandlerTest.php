<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Inbox;

use App\Application\Inbox\UseCases\CreateInboxItemCommand;
use App\Application\Inbox\UseCases\CreateInboxItemHandler;
use App\Domain\Inbox\Repositories\InboxItemRepositoryInterface;
use App\Domain\Inbox\Services\InboxDomainService;
use App\Domain\Inbox\ValueObjects\InboxItem as DomainInboxItem;
use App\Domain\Shared\ValueObjects\UserId;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateInboxItemHandlerTest extends TestCase
{
    public function test_creates_inbox_item(): void
    {
        $expectedDomainItem = new DomainInboxItem(
            id: 1,
            userId: 1,
            workspaceId: 2,
            content: 'Test content',
            source: 'manual',
            isProcessed: false,
            convertedToCardId: null,
            aiSuggestions: null,
            createdAt: '2024-01-01 00:00:00',
            updatedAt: '2024-01-01 00:00:00',
        );

        $repo = $this->mock(InboxItemRepositoryInterface::class, function (MockInterface $mock) use ($expectedDomainItem) {
            $mock->shouldReceive('create')->once()->with(
                \Mockery::on(function (array $payload) {
                    return $payload['content'] === 'Test content'
                        && $payload['workspace_id'] === 2
                        && $payload['source'] === 'manual';
                }),
                \Mockery::on(function (UserId $userId) {
                    return $userId->value === 1;
                })
            )->andReturn($expectedDomainItem);
        });

        $inboxDomainService = new InboxDomainService;

        $handler = new CreateInboxItemHandler($repo, $inboxDomainService);
        $cmd = new CreateInboxItemCommand(userId: 1, workspaceId: 2, content: 'Test content', source: 'manual');
        $item = $handler->handle($cmd);

        $this->assertInstanceOf(DomainInboxItem::class, $item);
        $this->assertSame('Test content', $item->content);
        $this->assertSame(1, $item->userId);
        $this->assertSame(2, $item->workspaceId);
    }
}
