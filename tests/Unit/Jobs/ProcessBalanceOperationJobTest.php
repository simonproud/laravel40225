<?php

namespace Tests\Unit\Jobs;

use Tests\TestCase;
use App\Models\User;
use App\Jobs\ProcessBalanceOperationJob;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProcessBalanceOperationJobTest extends TestCase
{
    use RefreshDatabase;

    public function testHandle_WithNonExistentUser_ShouldThrowException()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Пользователь не найден');

        $job = new ProcessBalanceOperationJob(9999, 'deposit', 100.0);
        $job->handle(app(\App\Services\BalanceService::class));
    }

    public function testHandle_WithExistingUserAndDeposit_ShouldNotThrowException()
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => bcrypt('secret'),
        ]);

        $job = new ProcessBalanceOperationJob($user->id, 'deposit', 150.0);
        $job->handle(app(\App\Services\BalanceService::class));

        // Basic assertion to ensure no exception was thrown
        $this->assertTrue(true);
    }

    public function testHandle_WithExistingUserAndWithdraw_ShouldNotThrowException()
    {
        $user = User::create([
            'name' => 'Test User 2',
            'email' => 'test2@example.com',
            'password' => bcrypt('secret2'),
        ]);

        $job = new ProcessBalanceOperationJob($user->id, 'withdraw', 10.0);
        $job->handle(app(\App\Services\BalanceService::class));

        $this->assertTrue(true);
    }
}
