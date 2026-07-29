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

    public function test_responden_user_is_automatically_redirected_to_responden_dashboard_on_login(): void
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

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('responden.dashboard'));
    }

    public function test_pembuat_survey_user_is_automatically_redirected_to_user_dashboard_on_login(): void
    {
        $user = User::factory()->create([
            'email' => 'client@example.com',
            'password' => Hash::make('secret123'),
            'is_responden' => 0,
            'is_admin' => 0,
        ]);

        $response = $this->post(route('login.submit'), [
            'email' => 'client@example.com',
            'password' => 'secret123',
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('user.dashboard'));
    }

    public function test_registering_as_responden_sets_is_responden_true(): void
    {
        $response = $this->post(route('responden.register.submit'), [
            'nama' => 'Responden Baru',
            'email' => 'newresponden@example.com',
            'whatsapp_number' => '081234567890',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $user = User::where('email', 'newresponden@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals(1, $user->is_responden);
        $this->assertNotNull($user->wallet);
        $response->assertRedirect(route('responden.dashboard'));
    }

    public function test_registering_as_pembuat_survey_sets_is_responden_false(): void
    {
        $response = $this->post(route('register.post'), [
            'name' => 'Client Baru',
            'email' => 'newclient@example.com',
            'phone' => '081298765432',
            'password' => 'secret12345',
            'password_confirmation' => 'secret12345',
        ]);

        $user = User::where('email', 'newclient@example.com')->first();

        $this->assertNotNull($user);
        $this->assertEquals(0, $user->is_responden);
        $response->assertRedirect(route('user.dashboard'));
    }
}
