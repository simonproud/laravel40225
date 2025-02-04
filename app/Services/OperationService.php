<?php

namespace App\Services;

use App\Models\Operation;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Сервис для работы с операциями (Operation).
 *
 * Ответственность этого сервиса:
 *  - Получение операций пользователя с различными фильтрами,
 *  - Создание новых записей операций,
 *  - Работа с пагинацией (при необходимости).
 */
class OperationService
{
    /**
     * Получить ограниченное количество последних операций пользователя.
     *
     * @param  User  $user   Пользователь, чьи операции нужны
     * @param  int   $limit  Количество операций, которые нужно вернуть
     * @return Collection<Operation>
     */
    public function getLastOperations(User $user, int $limit = 5): Collection
    {
        return Operation::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Получить операции пользователя с пагинацией.
     *
     * @param  User  $user          Пользователь
     * @param  int   $perPage       Количество на страницу
     * @return LengthAwarePaginator
     */
    public function getOperationsPaginated(User $user, int $perPage = 10): LengthAwarePaginator
    {
        return DB::transaction(fn() => Operation::query()
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage));
    }
}
