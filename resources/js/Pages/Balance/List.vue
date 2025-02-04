<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

// Интервал обновления данных (в миллисекундах)
const REFRESH_INTERVAL = 5000;

// Хранение текущего баланса и списка последних операций
const balance = ref<number>(0);
const transactions = ref<any[]>([]);

// Функция для получения данных с сервера
async function fetchBalanceAndTransactions() {
    try {
        // Здесь можно заменить URL на реальный для получения баланса
        const { data: balanceData } = await axios.get('/balance');
        balance.value = balanceData.balance;
        transactions.value = balanceData.operations;
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
    }
}

// Компонент монтируется
onMounted(() => {
    // Загружаем данные при монтировании
    fetchBalanceAndTransactions();

    // Запускаем интервальное обновление
    const intervalId = setInterval(
        fetchBalanceAndTransactions,
        REFRESH_INTERVAL,
    );

    // Очищаем интервал при размонтировании компонента
    onUnmounted(() => {
        clearInterval(intervalId);
    });
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="balance-container">
            <h1 class="balance-title">
                Текущий баланс:
                <span class="balance-amount">{{ balance }} ₽</span>
            </h1>

            <h2 class="transactions-title">Последние 5 операций</h2>
            <ul class="transactions-list">
                <li
                    v-for="(transaction, index) in transactions"
                    :key="index"
                    class="transaction-item"
                >
                    <div class="transaction-details">
                        <div class="transaction-description">
                            <strong>Тип:</strong>
                            <span>{{
                                transaction.operation_type === 'debit'
                                    ? 'Пополнение'
                                    : 'Списание'
                            }}</span>
                        </div>
                        <div class="transaction-amount">
                            <strong>Сумма:</strong>
                            <span
                                :class="{
                                    'amount-positive': transaction.amount > 0,
                                    'amount-negative': transaction.amount < 0,
                                }"
                            >
                                {{ transaction.amount }} ₽
                            </span>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
h1 {
    margin-bottom: 1rem;
}

h2 {
    margin-top: 2rem;
    margin-bottom: 1rem;
}

ul {
    list-style-type: none;
    padding: 0;
}

li + li {
    margin-top: 0.5rem;
}
.balance-container {
    padding: 2rem;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.balance-title {
    font-size: 2rem;
    color: #2d3748;
}

.balance-amount {
    font-weight: bold;
    color: #2c5282;
}

.transactions-title {
    color: #4a5568;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.transactions-list {
    margin-top: 1rem;
}

.transaction-item {
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
}

.transaction-item:hover {
    background-color: #f7fafc;
    transform: translateX(4px);
}

.transaction-details {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.transaction-description {
    flex: 1;
}

.transaction-amount {
    margin-left: 1rem;
}

.amount-positive {
    color: #48bb78;
}

.amount-negative {
    color: #e53e3e;
}
</style>
