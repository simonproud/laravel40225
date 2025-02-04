<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Модель для работы с операциями (debit/credit).
 *
 * @property int $id
 * @property int $user_id
 * @property string $operation_type
 * @property float $amount
 */
class Operation extends Model
{
    use HasFactory;

    /**
     * Явно указываем связанную таблицу.
     *
     * @var string
     */
    protected $table = 'operations';

    /**
     * Запрещаем массовое заполнение критичных полей.
     *
     * @var list<string>
     */
    protected $guarded = ['id', 'updated_at', 'created_at'];

    /**
     * Пример использования кастов для поля amount.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'amount' => 'decimal:4',
    ];

    /**
     * Определяем связь "Operation принадлежит одному User".
     *
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
