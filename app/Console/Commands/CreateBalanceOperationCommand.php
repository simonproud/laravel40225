<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\ProcessBalanceOperationJob;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CreateBalanceOperationCommand extends Command
{
    protected $signature = 'balance:create-operation
                            {user_id : ID пользователя}
                            {type : Тип операции (deposit/withdraw)}
                            {amount : Сумма операции}';

    protected $description = 'Создание операции пополнения или списания через очередь';

    public function handle(): int
    {
        $userId = (int) $this->argument('user_id');
        $type   = $this->argument('type');
        $amount = (float) $this->argument('amount');
        if (!in_array($type, ['deposit', 'withdraw'], true)) {
            $this->error('Неверный тип операции. Допустимые: deposit, withdraw.');
            return CommandAlias::INVALID;
        }

        ProcessBalanceOperationJob::dispatch($userId, $type, $amount);
        $this->info("Операция '{$type}' на сумму {$amount} для пользователя с ID={$userId} добавлена в очередь.");

        return CommandAlias::SUCCESS;
    }
}
