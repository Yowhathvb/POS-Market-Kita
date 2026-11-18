<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    protected string $apiKey = 'pos_secret_token_1234567890';

    protected function setUp(): void
    {
        parent::setUp();
        config(['app.api_key' => $this->apiKey]);
        putenv("API_KEY={$this->apiKey}");
        $_ENV['API_KEY'] = $this->apiKey;
    }

    public function test_wallet_api_requires_api_key(): void
    {
        $response = $this->getJson('/v1/wallet');
        $response->assertStatus(401);
    }

    public function test_wallet_api_requires_user_key(): void
    {
        $user = User::factory()->create(['id_users' => 'POS-NOKEY01']);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->getJson('/v1/wallet');

        $response->assertStatus(401);
        $response->assertJsonPath('error', 'MISSING_USER_KEY');
    }

    public function test_user_can_view_wallet_info_and_balance(): void
    {
        $userKey = 'wallet_user_key_123';
        $user = User::factory()->create([
            'id_users' => 'POS-WALLET01',
            'key' => $userKey,
        ]);

        $wallet = $user->getOrCreateWallet();
        $wallet->balance = 500000;
        $wallet->pending_balance = 25000;
        $wallet->value = [
            'bank_account' => [
                'bank_name' => 'BCA',
                'account_number' => '8830192831',
                'account_holder' => 'Ahmad Dani',
            ],
            'mutations' => [
                [
                    'type' => 'credit',
                    'amount' => 125000,
                    'balance_before' => 375000,
                    'balance_after' => 500000,
                    'description' => 'Pembayaran lunas invoice INV-20261118-8F2A1B',
                    'timestamp' => '2026-11-18T10:15:30.000000Z',
                ]
            ],
        ];
        $wallet->save();

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->withHeader('X-USER-KEY', $userKey)
            ->getJson('/v1/wallet');

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.wallet.balance', 500000);
        $response->assertJsonPath('data.wallet.pending_balance', 25000);
        $response->assertJsonPath('data.wallet.currency', 'IDR');
        $response->assertJsonPath('data.wallet.bank_account.bank_name', 'BCA');
        $response->assertJsonPath('data.wallet.mutations.0.type', 'credit');
        $response->assertJsonPath('data.wallet.mutations.0.amount', 125000);
        $response->assertJsonPath('data.wallet.mutations.0.timestamp', '2026-11-18T10:15:30.000000Z');
        $response->assertJsonPath('data.balance', 500000);
        $response->assertJsonPath('data.pending_balance', 25000);
        $response->assertJsonPath('data.currency', 'IDR');
    }

    public function test_user_can_update_bank_account(): void
    {
        $userKey = 'wallet_bank_key_456';
        $user = User::factory()->create([
            'id_users' => 'POS-BANK01',
            'key' => $userKey,
        ]);

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/wallet/bank-account', [
                'key' => $userKey,
                'bank_name' => 'BCA',
                'account_number' => '8830123456',
                'account_holder' => 'Budi Santoso',
            ]);

        $response->assertStatus(200);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.bank_account.bank_name', 'BCA');
        $response->assertJsonPath('data.bank_account.account_number', '8830123456');
        $response->assertJsonPath('data.bank_account.account_holder', 'Budi Santoso');

        $wallet = $user->getOrCreateWallet();
        $this->assertEquals('BCA', $wallet->value['bank_account']['bank_name']);
        $this->assertEquals('8830123456', $wallet->value['bank_account']['account_number']);
    }

    public function test_user_can_request_withdrawal_if_balance_sufficient(): void
    {
        $userKey = 'wallet_wd_key_789';
        $user = User::factory()->create([
            'id_users' => 'POS-WD01',
            'key' => $userKey,
        ]);

        $wallet = $user->getOrCreateWallet();
        $wallet->balance = 100000;
        $wallet->value = [
            'bank_account' => [
                'bank_name' => 'MANDIRI',
                'account_number' => '1370012345678',
                'account_holder' => 'Budi Santoso',
            ]
        ];
        $wallet->save();

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/wallet/withdraw', [
                'key' => $userKey,
                'amount' => 50000,
            ]);

        $response->assertStatus(201);
        $response->assertJsonPath('status', 'success');
        $response->assertJsonPath('data.amount', 50000);
        $response->assertJsonPath('data.remaining_balance', 50000);
        $response->assertJsonPath('data.pending_balance', 50000);

        $wallet->refresh();
        $this->assertEquals(50000, (float) $wallet->balance);
        $this->assertEquals(50000, (float) $wallet->pending_balance);

        $this->assertDatabaseHas('withdrawals', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'amount' => '50000.00',
            'status' => 'pending',
        ]);
    }

    public function test_withdrawal_fails_if_balance_insufficient(): void
    {
        $userKey = 'wallet_insufficient_key';
        $user = User::factory()->create([
            'id_users' => 'POS-WD02',
            'key' => $userKey,
        ]);

        $wallet = $user->getOrCreateWallet();
        $wallet->balance = 20000;
        $wallet->value = [
            'bank_account' => [
                'bank_name' => 'BRI',
                'account_number' => '001201234567',
                'account_holder' => 'Budi',
            ]
        ];
        $wallet->save();

        $response = $this->withHeader('X-API-KEY', $this->apiKey)
            ->postJson('/v1/wallet/withdraw', [
                'key' => $userKey,
                'amount' => 50000,
            ]);

        $response->assertStatus(400);
        $response->assertJsonPath('error', 'INSUFFICIENT_BALANCE');
    }
}
