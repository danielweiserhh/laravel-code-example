<?php

declare(strict_types=1);

namespace App\Domain\Users\Entities;

use App\Domain\Exceptions\InvariantViolationException;
use DateTimeImmutable;

final class UserDailyPlanEntity
{
    public function __construct(
        public readonly int $id,
        public readonly int $userId,
        public readonly string $planDate,
        public readonly ?array $bigThree,
        public readonly ?string $noteForUser,
        public readonly ?string $aiJobId,
        public readonly DateTimeImmutable $createdAt,
        public readonly DateTimeImmutable $updatedAt,
    ) {
        $this->validate();
    }

    private function validate(): void
    {
        if ($this->id <= 0) {
            throw new InvariantViolationException(
                'user_daily_plan.id.positive',
                'UserDailyPlan ID must be positive',
                ['id' => $this->id]
            );
        }

        if ($this->userId <= 0) {
            throw new InvariantViolationException(
                'user_daily_plan.user_id.positive',
                'UserDailyPlan user ID must be positive',
                ['user_id' => $this->userId]
            );
        }

        if (empty(trim($this->planDate))) {
            throw new InvariantViolationException(
                'user_daily_plan.plan_date.not_empty',
                'UserDailyPlan plan date cannot be empty',
                ['plan_date' => $this->planDate]
            );
        }
    }

    public function withBigThree(?array $newBigThree): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            planDate: $this->planDate,
            bigThree: $newBigThree,
            noteForUser: $this->noteForUser,
            aiJobId: $this->aiJobId,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }

    public function withNoteForUser(?string $newNote): self
    {
        return new self(
            id: $this->id,
            userId: $this->userId,
            planDate: $this->planDate,
            bigThree: $this->bigThree,
            noteForUser: $newNote,
            aiJobId: $this->aiJobId,
            createdAt: $this->createdAt,
            updatedAt: new DateTimeImmutable(),
        );
    }
}
