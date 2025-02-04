<?php

namespace Tests\Unit\Services;

use Tests\TestCase;
use App\Models\User;
use App\Models\Balance;
use App\Models\Operation;
use App\Services\BalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class BalanceServiceTest extends TestCase
{
    use RefreshDatabase;

    protected BalanceService $balanceService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->balanceService = app(BalanceService::class);
    }

    public function testGetBalance_NoBalanceRecord_ReturnsZero()
    {
        $user = User::factory()->create();

        $actualBalance = $this->balanceService->getBalance($user);
        $this->assertSame(0.0, $actualBalance);
    }

    public function testGetBalance_WithExistingBalance_ReturnsCorrectBalance()
    {
        $user = User::factory()->create();
        Operation::factory()->create([
            'user_id' => $user->id,
            'operation_type' => 'debit',
            'amount' => 123.45
        ]);

        $actualBalance = $this->balanceService->getBalance($user);
        $this->assertEquals(123.45, $actualBalance);
    }

    public function testCheckBalance_WhenUserHasSufficientFunds_ReturnsTrue()
    {
        $user = User::factory()->create();
        Operation::factory()->create([
            'user_id' => $user->id,
            'operation_type' => 'debit',
            'amount' => 150.0
        ]);

        $this->assertTrue($this->balanceService->checkBalance($user, 150.0));
    }

    public function testCheckBalance_WhenUserHasInsufficientFunds_ReturnsFalse()
    {
        $user = User::factory()->create();
        Operation::factory()->create([
            'user_id' => $user->id,
            'operation_type' => 'debit',
            'amount' => 50.0
        ]);

        $this->assertFalse($this->balanceService->checkBalance($user, 51.0));
    }

    public function testDeposit_CreatesOperation()
    {
        $user = User::factory()->create();
        Operation::factory()->create([
            'user_id' => $user->id,
            'operation_type' => 'debit',
            'amount' => 50.0
        ]);
        $operation = $this->balanceService->deposit($user, 1.0);
        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals('debit', $operation->operation_type);
        $this->assertEquals(1.0, $operation->amount);
        $this->assertEquals($user->id, $operation->user_id);
    }

    public function testWithdraw_CreatesOperation()
    {
        $user = User::factory()->create();

        $operation = $this->balanceService->withdraw($user, 50.0);
        $this->assertInstanceOf(Operation::class, $operation);
        $this->assertEquals('credit', $operation->operation_type);
        $this->assertEquals(50.0, $operation->amount);
        $this->assertEquals($user->id, $operation->user_id);
    }
}
