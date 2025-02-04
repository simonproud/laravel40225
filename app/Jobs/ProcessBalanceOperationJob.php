<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\BalanceService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
class ProcessBalanceOperationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;


    protected int $userId;
    protected string $operationType;
    protected float $amount;


    public function __construct(int $userId, string $operationType, float $amount)
    {
        $this->userId = $userId;
        $this->operationType = $operationType;
        $this->amount = $amount;
    }

    public function handle(BalanceService $balanceService)
    {
        $user = User::find($this->userId);

        if (!$user) {
            throw new \Exception('Пользователь не найден');
        }
        if ($this->operationType === 'deposit') {
            $balanceService->deposit($user, $this->amount);
        } elseif (
            $this->operationType === 'withdraw' &&
            $balanceService->checkBalance($user, $this->amount)
        ) {
            $balanceService->withdraw($user, $this->amount);
        }
    }
}
