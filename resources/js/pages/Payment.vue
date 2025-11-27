<script setup lang="ts">
import { ref, onUnmounted } from 'vue';
import { Head, router } from '@inertiajs/vue3';

interface OrderItem {
    serial: number;
    product: string;
    quantity: number;
    price: number;
    discount: number;
    total: number;
}

const form = ref({
    nombre_cliente: '',
    email: '',
    telefono: '',
    monto: 0,
    detalle_pedido: [] as OrderItem[],
});

const showQR = ref(false);
const qrImage = ref('');
const idTransaccion = ref<number | null>(null);
const loading = ref(false);
const error = ref('');
const paymentCompleted = ref(false);
let statusCheckInterval: number | null = null;

// Añadir item de ejemplo
const addSampleItem = () => {
    const total = Number(form.value.monto) || 0.1;
    form.value.detalle_pedido = [
        {
            serial: 1,
            product: 'Producto de prueba',
            quantity: 1,
            price: total,
            discount: 0,
            total: total,
        },
    ];
};

const createPayment = async () => {
    if (!form.value.nombre_cliente || !form.value.email || !form.value.telefono || !form.value.monto) {
        error.value = 'Por favor completa todos los campos';
        return;
    }

    loading.value = true;
    error.value = '';

    // Añadir item automáticamente
    addSampleItem();

    try {
        const response = await fetch('/pago/crear', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify(form.value),
        });

        const data = await response.json();

        if (data.success) {
            qrImage.value = `data:image/png;base64,${data.transaccion.qr_base64}`;
            idTransaccion.value = data.transaccion.id;
            showQR.value = true;
            startStatusCheck();
        } else {
            error.value = data.message || 'Error al generar QR';
        }
    } catch (err) {
        error.value = 'Error de conexión';
    } finally {
        loading.value = false;
    }
};

const checkStatus = async () => {
    if (!idTransaccion.value) return;

    try {
        const response = await fetch(`/pago/estado/${idTransaccion.value}`);
        const data = await response.json();

        if (data.status === 'paid') {
            paymentCompleted.value = true;
            stopStatusCheck();
        }
    } catch (err) {
        console.error('Error al verificar el estado:', err);
    }
};

const startStatusCheck = () => {
    statusCheckInterval = window.setInterval(checkStatus, 3000); // Cada 3 segundos
};

const stopStatusCheck = () => {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
        statusCheckInterval = null;
    }
};

const reset = () => {
    showQR.value = false;
    qrImage.value = '';
    idTransaccion.value = null;
    paymentCompleted.value = false;
    form.value = {
        nombre_cliente: '',
        email: '',
        telefono: '',
        monto: 0,
        detalle_pedido: [],
    };
    stopStatusCheck();
};

onUnmounted(() => {
    stopStatusCheck();
});
</script>

<template>
    <Head title="Realizar Pago">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>

    <div class="min-h-screen bg-gradient-to-br from-blue-50 to-indigo-100 dark:from-gray-900 dark:to-gray-800 p-6 flex items-center justify-center">
        <div class="max-w-md w-full bg-white dark:bg-gray-800 rounded-2xl shadow-2xl p-8">
            <!-- Header -->
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">PagoFácil</h1>
                <p class="text-gray-600 dark:text-gray-400">Paga con código QR</p>
            </div>

            <!-- Formulario o QR -->
            <div v-if="!showQR">
                <!-- Error -->
                <div v-if="error" class="mb-4 p-4 bg-red-50 border border-red-200 text-red-700 rounded-lg">
                    {{ error }}
                </div>

                <!-- Formulario -->
                <form @submit.prevent="createPayment" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Nombre completo
                        </label>
                        <input
                            v-model="form.nombre_cliente"
                            type="text"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="Juan Pérez"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Email
                        </label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="juan@ejemplo.com"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Teléfono
                        </label>
                        <input
                            v-model="form.telefono"
                            type="tel"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="75540850"
                        />
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Monto (BOB)
                        </label>
                        <input
                            v-model.number="form.monto"
                            type="number"
                            step="0.01"
                            min="0.1"
                            required
                            class="w-full px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg focus:ring-2 focus:ring-blue-500 dark:bg-gray-700 dark:text-white"
                            placeholder="0.10"
                        />
                    </div>

                    <button
                        type="submit"
                        :disabled="loading"
                        class="w-full bg-blue-600 hover:bg-blue-700 disabled:bg-gray-400 text-white font-semibold py-3 px-4 rounded-lg transition duration-200 transform hover:scale-105 disabled:hover:scale-100"
                    >
                        {{ loading ? 'Generando QR...' : 'Generar QR' }}
                    </button>
                </form>
            </div>

            <!-- QR Code Display -->
            <div v-else class="text-center">
                <!-- Payment Complete -->
                <div v-if="paymentCompleted" class="space-y-4">
                    <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto">
                        <svg class="w-12 h-12 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <h2 class="text-2xl font-bold text-green-600">¡Pago Completado!</h2>
                    <p class="text-gray-600 dark:text-gray-400">Tu pago ha sido procesado exitosamente</p>
                    <button
                        @click="reset"
                        class="mt-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200"
                    >
                        Nuevo Pago
                    </button>
                </div>

                <!-- QR Pending -->
                <div v-else class="space-y-4">
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Escanea el QR</h2>
                    
                    <div class="bg-white p-4 rounded-xl inline-block">
                        <img :src="qrImage" alt="QR Code" class="w-64 h-64 mx-auto" />
                    </div>

                    <div class="flex items-center justify-center space-x-2 mt-4">
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.1s"></div>
                        <div class="w-2 h-2 bg-blue-600 rounded-full animate-bounce" style="animation-delay: 0.2s"></div>
                    </div>
                    
                    <p class="text-gray-600 dark:text-gray-400">Esperando confirmación del pago...</p>

                    <button
                        @click="reset"
                        class="mt-4 text-gray-600 hover:text-gray-800 dark:text-gray-400 dark:hover:text-gray-200 underline"
                    >
                        Cancelar
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
