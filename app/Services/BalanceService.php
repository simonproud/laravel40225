<?php

namespace App\Services;

use App\Models\Balance;
use App\Models\Operation;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с балансом пользователя.
 *
 * Ответственность этого сервиса:
 *  - Пополнение и списание средств путём создания операций (Operation).
 *  - Чтение текущего баланса.
 *  - Обеспечение целостности данных через транзакции.
 */
class BalanceService
{
    /**
     * Получить текущий баланс пользователя.
     *
     * @param  User  $user  Пользователь, для которого запрашивается баланс
     * @return float         Текущее значение баланса
     */
    public function getBalance(User $user): float
    {
        /** @var Balance|null $balanceModel */
        $balanceModel = $user->balance()->first();

        return $balanceModel?->balance ?? 0.0;
    }

    /**
     * Проверить возможность списания средств со счёта пользователя.
     *
     * @param User $user Пользователь, для которого запрашивается баланс
     * @param float $amount Сумма списания
     * @return bool Возможность списания средств
     */
    public function checkBalance(User $user, float $amount): bool
    {
       return $this->getBalance($user) >= $amount;
    }

    /**
     * Пополнить баланс пользователя (создать операцию "debit").
     *
     * @param  User   $user   Пользователь, чей баланс нужно пополнить
     * @param  float  $amount Сумма пополнения
     * @return Operation      Созданная модель операции
     */
    public function deposit(User $user, float $amount): Operation
    {
        return DB::transaction(function () use ($user, $amount) {
            return Operation::create([
                'user_id'        => $user->id,
                'operation_type' => 'debit',
                'amount'         => $amount,
            ]);
        });
    }

    /**
     * Списать средства с баланса пользователя (создать операцию "credit").
     *
     * @param  User   $user   Пользователь, с чьего баланса нужно списать средства
     * @param  float  $amount Сумма списания
     * @return Operation      Созданная модель операции
     */
    public function withdraw(User $user, float $amount): Operation
    {
        $mutexKey = 'user_balance:' . $user->id;
        $lock = Cache::lock($mutexKey, 10);

        return $lock->block(5, function () use ($user, $amount) {
            return DB::transaction(function () use ($user, $amount) {
                return Operation::create([
                    'user_id' => $user->id,
                    'operation_type' => 'credit',
                    'amount' => $amount,
                ]);
            });
        });
    }
}
