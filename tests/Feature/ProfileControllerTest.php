<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_profile(): void
    {
        $user = User::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/profile');

        $response->assertOk()
            ->assertJsonPath('data.id', $user->id)
            ->assertJsonPath('data.email', $user->email);
    }

    public function test_user_can_update_profile_information(): void
    {
        $user = User::factory()->create([
            'name' => 'Old Name',
            'phone' => null,
            'telegram_username' => null,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/v1/profile', [
            'name' => 'New Name',
            'phone' => '+7 (999) 123-45-67',
            'telegram_username' => '@new_handle',
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.phone', '+7 (999) 123-45-67')
            ->assertJsonPath('data.telegram_username', 'new_handle')
            ->assertJsonPath('message', 'Профиль обновлён успешно');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'New Name',
            'phone' => '+7 (999) 123-45-67',
            'telegram_username' => 'new_handle',
        ]);
    }

    public function test_user_can_update_password_and_other_tokens_are_revoked(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('OldPassword1!'),
        ]);

        $otherToken = $user->createToken('other-device');
        $actingToken = $user->createToken('current-device');
        
        Sanctum::actingAs($user, abilities: ['*']);
        $user->withAccessToken($actingToken->accessToken);

        $response = $this->putJson('/api/v1/profile/password', [
            'current_password' => 'OldPassword1!',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertOk()
            ->assertJsonPath('message', 'Пароль обновлён успешно');

        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
        
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $otherToken->accessToken->id,
        ]);
        
        $this->assertDatabaseHas('personal_access_tokens', [
            'id' => $actingToken->accessToken->id,
        ]);
    }
}
