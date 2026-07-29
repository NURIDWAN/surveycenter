<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleBasedAccessControlTest extends TestCase
{
    use RefreshDatabase;

    public function test_pembuat_survey_user_cannot_access_responden_dashboard(): void
    {
        $user = User::factory()->create([
            'is_responden' => 0,
            'is_admin' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('responden.dashboard'));

        $response->assertRedirect(route('user.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_responden_user_cannot_access_pembuat_survey_dashboard(): void
    {
        $user = User::factory()->create([
            'is_responden' => 1,
            'is_admin' => 0,
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard'));

        $response->assertRedirect(route('responden.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_responden_user_cannot_login_via_pembuat_survey_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'responden@example.com',
            'password' => Hash::make('secret123'),
            'is_responden' => 1,
            'is_admin' => 0,
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'responden@example.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_pembuat_survey_user_cannot_login_via_responden_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('secret123'),
            'is_responden' => 0,
            'is_admin' => 0,
        ]);

        $response = $this->post(route('responden.login.submit'), [
            'email' => 'client@example.com',
            'password' => 'secret123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');

        $user->refresh();
        $this->assertEquals(0, $user->is_responden);
    }

    public function test_pembuat_survey_user_can_login_via_pembuat_survey_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'client2@example.com',
            'password' => Hash::make('secret123'),
            'is_responden' => 0,
            'is_admin' => 0,
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'client2@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_responden_user_can_login_via_responden_login_form(): void
    {
        $user = User::factory()->create([
            'email' => 'responden2@example.com',
            'password' => Hash::make('secret123'),
            'is_responden' => 1,
            'is_admin' => 0,
        ]);

        $response = $this->post(route('responden.login.submit'), [
            'email' => 'responden2@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('responden.dashboard'));
    }
}
