<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель для работы с балансами пользователей.
 *
 * @property int $id
 * @property int $user_id
 * @property float $balance
 */
class Balance extends Model
{
    use HasFactory;

    /**
     * Явно указываем связанную таблицу (не обязательно, если имя модели совпадает с таблицей).
     *
     * @var string
     */
    protected $table = 'balances';

    /**
     * Запрещаем массовое заполнение критичных полей.
     *
     * @var list<string>
     */
    protected $guarded = ['id', 'updated_at', 'created_at'];

    /**
     * Определяем связь "Balance принадлежит одному User".
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Переопределяем метод performUpdate, чтобы запретить UPDATE баланса напрямую.
     *
     * @param  Builder      $query
     * @param  array<mixed> $options
     * @return never
     *
     * @throws \RuntimeException
     */
    protected function performUpdate(Builder $query, array $options = []): bool
    {
        throw new \RuntimeException('Нельзя напрямую обновлять баланс через модель Balance.');
    }
}
