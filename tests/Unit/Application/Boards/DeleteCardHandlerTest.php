<?php

declare(strict_types=1);

namespace Tests\Unit\Application\Boards;

use App\Application\Boards\UseCases\DeleteCardCommand;
use App\Application\Boards\UseCases\DeleteCardHandler;
use App\Domain\Boards\Repositories\CardRepositoryInterface;
use Mockery\MockInterface;
use Tests\TestCase;

class DeleteCardHandlerTest extends TestCase
{
    public function test_deletes_card_via_repository(): void
    {
        $cardId = 123;

        $cardRepository = $this->mock(CardRepositoryInterface::class, function (MockInterface $mock) use ($cardId) {
            $mock->shouldReceive('delete')->once()->with($cardId);
        });

        $handler = new DeleteCardHandler($cardRepository);
        $command = new DeleteCardCommand(cardId: $cardId);

        $handler->handle($command);
    }
}
