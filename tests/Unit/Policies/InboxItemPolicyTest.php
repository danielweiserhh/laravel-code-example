<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Models\InboxItem;
use App\Models\User;
use App\Policies\InboxItemPolicy;
use PHPUnit\Framework\TestCase;

class InboxItemPolicyTest extends TestCase
{
    private InboxItemPolicy $policy;
    private User $user;
    private InboxItem $inboxItem;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new InboxItemPolicy;
        
        $this->user = new User;
        $this->user->id = 1;

        $this->inboxItem = new InboxItem;
        $this->inboxItem->user_id = 1;
    }

    public function test_view_any_returns_true(): void
    {
        $this->assertTrue($this->policy->viewAny($this->user));
    }

    public function test_view_returns_true_when_user_owns_item(): void
    {
        $this->inboxItem->user_id = 1;
        $this->assertTrue($this->policy->view($this->user, $this->inboxItem));
    }

    public function test_view_returns_false_when_user_does_not_own_item(): void
    {
        $this->inboxItem->user_id = 2;
        $this->assertFalse($this->policy->view($this->user, $this->inboxItem));
    }

    public function test_create_returns_true(): void
    {
        $this->assertTrue($this->policy->create($this->user));
    }

    public function test_update_returns_true_when_user_owns_item(): void
    {
        $this->inboxItem->user_id = 1;
        $this->assertTrue($this->policy->update($this->user, $this->inboxItem));
    }

    public function test_update_returns_false_when_user_does_not_own_item(): void
    {
        $this->inboxItem->user_id = 2;
        $this->assertFalse($this->policy->update($this->user, $this->inboxItem));
    }

    public function test_delete_returns_true_when_user_owns_item(): void
    {
        $this->inboxItem->user_id = 1;
        $this->assertTrue($this->policy->delete($this->user, $this->inboxItem));
    }

    public function test_delete_returns_false_when_user_does_not_own_item(): void
    {
        $this->inboxItem->user_id = 2;
        $this->assertFalse($this->policy->delete($this->user, $this->inboxItem));
    }
}
