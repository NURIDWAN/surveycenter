<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Survey;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminTransactionEditTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_edit_transaction_page(): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $client = User::factory()->create(['is_admin' => 0]);
        $survey = Survey::create([
            'user_id' => $client->id,
            'title' => 'Test Survey',
            'question_count' => 5,
        ]);
        $transaction = Transaction::create([
            'user_id' => $client->id,
            'survey_id' => $survey->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $queries = [];
        DB::listen(function ($query) use (&$queries): void {
            $queries[] = str_replace(['`', '"'], '', strtolower($query->sql));
        });

        $response = $this->actingAs($admin)->get(route('admin.transactions.edit', $transaction));

        $response->assertOk();
        $response->assertSee('Test Survey');
        $response->assertSee($client->name);
        $this->assertFalse(
            collect($queries)->contains(fn (string $query): bool => str_contains($query, 'order by name')),
            'The edit page must not load the complete users table.'
        );
        $this->assertFalse(
            collect($queries)->contains(fn (string $query): bool => str_contains($query, 'order by title')),
            'The edit page must not load the complete surveys table.'
        );
    }

    public function test_admin_can_update_transaction(): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $client = User::factory()->create(['is_admin' => 0]);
        $survey = Survey::create([
            'user_id' => $client->id,
            'title' => 'Test Survey',
            'question_count' => 5,
            'status' => Survey::STATUS_DRAFT,
        ]);
        $transaction = Transaction::create([
            'user_id' => $client->id,
            'survey_id' => $survey->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.transactions.update', $transaction), [
            'survey_id' => null,
            'user_id' => $admin->id,
            'amount' => 60000,
            'status' => 'paid',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('admin.transactions.edit', $transaction));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'survey_id' => $survey->id,
            'user_id' => $client->id,
            'amount' => 50000,
            'status' => 'paid',
            'payment_method' => null,
        ]);
        $this->assertDatabaseHas('surveys', [
            'id' => $survey->id,
            'status' => Survey::STATUS_ACTIVE,
        ]);
    }

    public function test_admin_can_access_transaction_progress_edit(): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $client = User::factory()->create(['is_admin' => 0]);
        $survey = Survey::create([
            'user_id' => $client->id,
            'title' => 'Test Survey',
            'question_count' => 5,
        ]);
        $transaction = Transaction::create([
            'user_id' => $client->id,
            'survey_id' => $survey->id,
            'amount' => 50000,
            'status' => 'paid',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.transactions.progress.edit', $transaction));

        $response->assertOk();
    }

    public function test_admin_can_update_transaction_progress(): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $client = User::factory()->create(['is_admin' => 0]);
        $survey = Survey::create([
            'user_id' => $client->id,
            'title' => 'Test Survey',
            'question_count' => 5,
        ]);
        $transaction = Transaction::create([
            'user_id' => $client->id,
            'survey_id' => $survey->id,
            'amount' => 50000,
            'status' => 'paid',
            'progress' => 0,
        ]);

        $response = $this->actingAs($admin)->put(route('admin.transactions.progress.update', $transaction), [
            'progress' => 50,
        ]);

        $response->assertRedirect(route('admin.transactions.progress.index'));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'progress' => 50,
        ]);
    }

    public function test_admin_can_update_transaction_without_user(): void
    {
        $admin = User::factory()->create(['is_admin' => 1]);
        $survey = Survey::create([
            'user_id' => $admin->id,
            'title' => 'Test Survey',
            'question_count' => 5,
        ]);
        $transaction = Transaction::create([
            'user_id' => null,
            'survey_id' => $survey->id,
            'amount' => 50000,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->put(route('admin.transactions.update', $transaction), [
            'amount' => 75000,
            'status' => 'processing',
            'payment_method' => 'bank_transfer',
        ]);

        $response->assertRedirect(route('admin.transactions.edit', $transaction));
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => 50000,
            'status' => 'processing',
            'payment_method' => null,
        ]);
    }
}
