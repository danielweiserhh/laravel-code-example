<?php

declare(strict_types=1);

namespace App\Domain\Boards\Repositories;

use App\Domain\Boards\ValueObjects\Card as DomainCard;


interface CardRepositoryInterface
{
    public function find(int $id): ?DomainCard;

    public function findOrFail(int $id): DomainCard;
    
    public function findWithBoard(int $id): ?DomainCard;
    
    public function findOrFailWithBoard(int $id): DomainCard;
    
    public function create(array $payload): DomainCard;
    
    public function update(int $id, array $payload): DomainCard;
    
    public function save(DomainCard $card): DomainCard;
    
    public function delete(int $id): void;
    
    public function getMaxPositionForList(int $listId): int;
    
    public function getMaxPositionForListExcluding(int $listId, ?int $excludeCardId = null): int;
    
    public function getByList(int $listId): array;
    
    public function updatePositions(array $positionMap): void;
    
    public function getActiveCardsForUser(int $userId): array;
}
