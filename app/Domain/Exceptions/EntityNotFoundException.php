<?php

declare(strict_types=1);

namespace App\Domain\Exceptions;

class EntityNotFoundException extends DomainException
{
    public function __construct(
        protected readonly string $entityType,
        protected readonly int|string $entityId,
        ?string $message = null
    ) {
        $message ??= sprintf('%s with ID %s not found', $entityType, $entityId);

        parent::__construct($message, [
            'entity_type' => $entityType,
            'entity_id' => $entityId,
        ]);
    }

    public function getEntityType(): string
    {
        return $this->entityType;
    }

    public function getEntityId(): int|string
    {
        return $this->entityId;
    }
}
