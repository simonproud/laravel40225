<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import axios from 'axios';
import { onMounted, onUnmounted, ref } from 'vue';

// Интервал обновления данных (в миллисекундах)
const REFRESH_INTERVAL = 5000;

// Текущий баланс
const balance = ref<number>(0);

// Данные пагинации операций
const operations = ref<any>(null);

// Список операций (из operations.data)
const transactions = ref<any[]>([]);

// Функция для получения данных с сервера
async function fetchBalanceAndTransactions(page = 1) {
    try {
        const { data } = await axios.get(`/operations/get?page=${page}`);

        balance.value = data.balance;
        operations.value = data.operations;
        transactions.value = data.operations.data;
    } catch (error) {
        console.error('Ошибка при загрузке данных:', error);
    }
}

// Переключение на предыдущую страницу
function previousPage() {
    if (operations.value?.prev_page_url) {
        const prevPage = operations.value.current_page - 1;
        fetchBalanceAndTransactions(prevPage);
    }
}

// Переключение на следующую страницу
function nextPage() {
    if (operations.value?.next_page_url) {
        const nextPage = operations.value.current_page + 1;
        fetchBalanceAndTransactions(nextPage);
    }
}

// При монтировании компонента запрашиваем данные и запускаем автообновление
onMounted(() => {
    fetchBalanceAndTransactions();

    const intervalId = setInterval(() => {
        fetchBalanceAndTransactions(operations.value?.current_page ?? 1);
    }, REFRESH_INTERVAL);

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

            <h2 class="transactions-title">
                Последние операции (с пагинацией)
            </h2>
            <ul class="transactions-list">
                <li
                    v-for="(transaction) in transactions"
                    :key="transaction.id"
                    class="transaction-item"
                >
                    <div class="transaction-details">
                        <div class="transaction-description">
                            <strong>Тип:</strong>
                            <span>
                                {{
                                    transaction.operation_type === 'debit'
                                        ? 'Пополнение'
                                        : 'Списание'
                                }}
                            </span>
                        </div>
                        <div class="transaction-amount">
                            <strong>Сумма:</strong>
                            <span
                                :class="{
                                    'amount-positive':
                                        transaction.operation_type === 'debit',
                                    'amount-negative':
                                        transaction.operation_type === 'credit',
                                }"
                            >
                                {{ transaction.amount }} ₽
                            </span>
                        </div>
                        <div class="transaction-time">
                            <strong>Дата:</strong>
                            {{ transaction.created_at }}
                        </div>
                    </div>
                </li>
            </ul>

            <!-- Кнопки пагинации -->
            <div v-if="operations" class="pagination-buttons">
                <button v-if="operations.prev_page_url" @click="previousPage">
                    &laquo; Previous
                </button>
                <span
                    >Страница {{ operations.current_page }} из
                    {{ operations.last_page }}</span
                >
                <button v-if="operations.next_page_url" @click="nextPage">
                    Next &raquo;
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
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
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #4a5568;
    border-bottom: 2px solid #e2e8f0;
    padding-bottom: 0.5rem;
}

.transactions-list {
    list-style-type: none;
    padding: 0;
}

.transaction-item {
    padding: 1rem;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    transition: all 0.2s ease;
    margin-bottom: 0.5rem;
}

.transaction-item:hover {
    background-color: #f7fafc;
    transform: translateX(4px);
}

.transaction-details {
    display: flex;
    flex-wrap: wrap;
    gap: 1rem;
}

.amount-positive {
    color: #48bb78;
}

.amount-negative {
    color: #e53e3e;
}

.pagination-buttons {
    margin-top: 1rem;
    display: flex;
    gap: 1rem;
    align-items: center;
}
</style>
