<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\Routine;
use App\Models\User;
use App\Policies\RoutinePolicy;
use PHPUnit\Framework\TestCase;

class RoutinePolicyTest extends TestCase
{
    private RoutinePolicy $policy;
    private User $user;
    private Routine $routine;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RoutinePolicy;

        $this->user = new User;
        $this->user->id = 1;

        $this->routine = new Routine;
        $this->routine->user_id = 1;
    }

    public function test_view_any_returns_true(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_owns_routine(): void
    {
        $this->routine->user_id = 1;
        $this->assertTrue($this->policy->view($this->user, $this->routine));
    }

    public function test_view_returns_false_when_user_does_not_own_routine(): void
    {
        $this->routine->user_id = 2;
        $this->assertFalse($this->policy->view($this->user, $this->routine));
    }

    public function test_create_returns_true(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_owns_routine(): void
    {
        $this->routine->user_id = 1;
        $this->assertTrue($this->policy->update($this->user, $this->routine));
    }

    public function test_update_returns_false_when_user_does_not_own_routine(): void
    {
        $this->routine->user_id = 2;
        $this->assertFalse($this->policy->update($this->user, $this->routine));
    }

    public function test_delete_returns_true_when_user_owns_routine(): void
    {
        $this->routine->user_id = 1;
        $this->assertTrue($this->policy->delete($this->user, $this->routine));
    }

    public function test_delete_returns_false_when_user_does_not_own_routine(): void
    {
        $this->routine->user_id = 2;
        $this->assertFalse($this->policy->delete($this->user, $this->routine));
    }
}
