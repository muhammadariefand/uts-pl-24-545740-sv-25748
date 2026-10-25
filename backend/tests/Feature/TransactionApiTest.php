<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TransactionApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_transaction(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'type' => 'income',
                'title' => 'Iuran anggota',
                'description' => 'Iuran bulan Oktober',
                'amount' => 250000,
                'transaction_date' => '2026-10-20',
            ]);

        $response
            ->assertCreated()
            ->assertJsonPath('data.title', 'Iuran anggota');

        $this->assertDatabaseHas('transactions', [
            'user_id' => $user->id,
            'title' => 'Iuran anggota',
            'amount' => 250000,
        ]);
    }

    public function test_transaction_validation_returns_422(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user, 'sanctum')
            ->postJson('/api/transactions', [
                'type' => 'invalid-type',
                'title' => '',
                'amount' => 0,
            ]);

        $response->assertUnprocessable();
    }
}