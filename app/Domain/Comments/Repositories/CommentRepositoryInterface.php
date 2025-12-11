<?php

declare(strict_types=1);

namespace App\Domain\Comments\Repositories;

use App\Domain\Comments\Collections\CommentCollection;
use App\Domain\Comments\Entities\CommentEntity;

interface CommentRepositoryInterface
{
    public function find(int $id): ?CommentEntity;

    public function findOrFail(int $id): CommentEntity;

    public function getByCard(int $cardId): CommentCollection;

    public function save(CommentEntity $comment): CommentEntity;

    public function delete(int $id): void;
}
