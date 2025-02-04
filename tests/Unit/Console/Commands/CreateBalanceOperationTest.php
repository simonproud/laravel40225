<?php

namespace Tests\Unit\Console\Commands;

use Tests\TestCase;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreateBalanceOperationTest extends TestCase
{
    /**
     * A basic unit test example.
     */
    public function testHandle_WithZeroDepositAmount_ShouldSucceed()
    {
        $this->artisan('balance:create-operation', [
            'user_id' => '1',
            'type'    => 'deposit',
            'amount'  => '0'
        ])
            ->assertExitCode(CommandAlias::SUCCESS)
            ->expectsOutput("Операция 'deposit' на сумму 0 для пользователя с ID=1 добавлена в очередь.");
    }
    public function testHandle_WithNegativeWithdrawAmount_ShouldSucceed()
    {
        // The command does not validate negative amounts, so it still succeeds
        $this->artisan('balance:create-operation', [
            'user_id' => '2',
            'type'    => 'withdraw',
            'amount'  => '-50'
        ])
            ->assertExitCode(CommandAlias::SUCCESS)
            ->expectsOutput("Операция 'withdraw' на сумму -50 для пользователя с ID=2 добавлена в очередь.");
    }

    public function testHandle_WithNonNumericUserId_ShouldSucceedAsZero()
    {
        // The command casts user_id to int, so this will end up as user_id=0
        $this->artisan('balance:create-operation', [
            'user_id' => 'abc',
            'type'    => 'deposit',
            'amount'  => '100'
        ])
            ->assertExitCode(CommandAlias::SUCCESS)
            ->expectsOutput("Операция 'deposit' на сумму 100 для пользователя с ID=0 добавлена в очередь.");
    }
}
