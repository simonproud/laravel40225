<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('balances', function (Blueprint $table) {
            $table->id();
            // Предполагаем одного пользователя - одна строка баланса
            $table->unsignedBigInteger('user_id')->unique();
            $table->decimal('balance', 20, 4)->default(0);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });

        Schema::create('operations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            // enum в PostgreSQL делается через отдельный тип, но упростим, воспользовавшись стандартным enum эмуляцией в Laravel
            $table->enum('operation_type', ['debit', 'credit']);
            $table->decimal('amount', 20, 4);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            // Индексы для оптимизации
            $table->index('user_id');
            // Для частых сортировок или фильтрации по дате
            $table->index(['user_id', 'created_at']);
        });

        // Создаём функцию для обновления баланса и триггер, вызывающий эту функцию после каждой вставки
        DB::unprepared(<<<SQL
         CREATE OR REPLACE FUNCTION update_user_balance()
        RETURNS TRIGGER
        LANGUAGE plpgsql
        SECURITY DEFINER
        AS \$\$
        BEGIN
            IF NEW.operation_type = 'debit' THEN
                UPDATE balances
                  SET balance = balance + NEW.amount
                  WHERE user_id = NEW.user_id;

                IF NOT FOUND THEN
                  INSERT INTO balances (user_id, balance, created_at, updated_at)
                  VALUES (NEW.user_id, NEW.amount, now(), now());
                END IF;

            ELSIF NEW.operation_type = 'credit' THEN
                UPDATE balances
                  SET balance = balance - NEW.amount
                  WHERE user_id = NEW.user_id;

                IF NOT FOUND THEN
                  INSERT INTO balances (user_id, balance, created_at, updated_at)
                  VALUES (NEW.user_id, (0 - NEW.amount), now(), now());
                END IF;

            END IF;

            RETURN NEW;
        END;
        \$\$;

        CREATE TRIGGER operation_insert_trigger
        AFTER INSERT ON operations
        FOR EACH ROW
        EXECUTE FUNCTION update_user_balance();
        SQL);

        // Запрещаем прямое изменение balances для PUBLIC
        // (При необходимости настройте права для конкретного пользователя/роли)
        DB::unprepared(<<<SQL
        REVOKE ALL PRIVILEGES ON balances FROM PUBLIC;
        GRANT SELECT ON balances TO PUBLIC;
        SQL);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Сначала удаляем триггер и функцию триггера
        DB::unprepared(<<<SQL
        DROP TRIGGER IF EXISTS operation_insert_trigger ON operations;
        DROP FUNCTION IF EXISTS update_user_balance();
        SQL);

        Schema::dropIfExists('operations');
        Schema::dropIfExists('balances');
    }
};
