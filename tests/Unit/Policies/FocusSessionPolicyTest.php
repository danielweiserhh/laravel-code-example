<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\FocusSession;
use App\Models\User;
use App\Policies\FocusSessionPolicy;
use PHPUnit\Framework\TestCase;

class FocusSessionPolicyTest extends TestCase
{
    private FocusSessionPolicy $policy;
    private User $user;
    private FocusSession $session;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new FocusSessionPolicy;

        $this->user = new User;
        $this->user->id = 1;

        $this->session = new FocusSession;
        $this->session->user_id = 1;
    }

    public function test_view_any_returns_true(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_owns_session(): void
    {
        $this->session->user_id = 1;
        $this->assertTrue($this->policy->view($this->user, $this->session));
    }

    public function test_create_returns_true(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_owns_session(): void
    {
        $this->session->user_id = 1;
        $this->assertTrue($this->policy->update($this->user, $this->session));
    }

    public function test_update_returns_false_when_user_does_not_own_session(): void
    {
        $this->session->user_id = 2;
        $this->assertFalse($this->policy->update($this->user, $this->session));
    }

    public function test_delete_returns_true_when_user_owns_session(): void
    {
        $this->session->user_id = 1;
        $this->assertTrue($this->policy->delete($this->user, $this->session));
    }

    public function test_delete_returns_false_when_user_does_not_own_session(): void
    {
        $this->session->user_id = 2;
        $this->assertFalse($this->policy->delete($this->user, $this->session));
    }
}
