<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { onMounted, onUnmounted, watch } from 'vue';

interface Producto {
    id: number;
    codigo: string;
    nombre: string;
}

interface DetalleVenta {
    id: number;
    producto: Producto;
    cantidad: number;
    precio_unitario: number;
    descuento: number;
    subtotal: number;
}

interface Pago {
    id: number;
    monto: number;
    metodo: string;
    estado: string;
    referencia: string | null;
    fecha_pago: string | null;
}

interface Usuario {
    id: number;
    name: string;
    email: string;
}

interface Venta {
    id: number;
    numero_venta: string;
    vendedor: Usuario;
    cliente: Usuario | null;
    tipo: string;
    estado: string;
    subtotal: number;
    descuento: number;
    total: number;
    observaciones: string | null;
    created_at: string;
    detalles: DetalleVenta[];
    pagos: Pago[];
}

const props = defineProps<{
    venta: Venta;
    qrImage?: string;
}>();

let pollInterval: ReturnType<typeof setInterval> | null = null;

const tienePagoPendiente = () => props.venta.pagos.some(p => p.estado === 'pendiente');

const iniciarPolling = () => {
    if (tienePagoPendiente() && !pollInterval) {
        pollInterval = setInterval(() => {
            router.reload({ only: ['venta'] });
        }, 5000);
    }
};

const detenerPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

onMounted(() => iniciarPolling());
onUnmounted(() => detenerPolling());

watch(() => props.venta.estado, (nuevoEstado) => {
    if (nuevoEstado === 'pagado' || nuevoEstado === 'cancelado') {
        detenerPolling();
    }
});

const estadoColor = (estado: string) => {
    const colores: Record<string, string> = {
        'pagado': 'bg-green-100 text-green-800',
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'cancelado': 'bg-red-100 text-red-800',
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};

const estadoPagoColor = (estado: string) => {
    const colores: Record<string, string> = {
        'completado': 'bg-green-100 text-green-800',
        'pendiente': 'bg-yellow-100 text-yellow-800',
        'rechazado': 'bg-red-100 text-red-800',
    };
    return colores[estado] || 'bg-gray-100 text-gray-800';
};

const estadoPagoTexto = (estado: string) => {
    const textos: Record<string, string> = {
        'completado': 'Completado',
        'pendiente': 'Pendiente',
        'rechazado': 'Rechazado',
    };
    return textos[estado] || estado;
};

const formatFecha = (fecha: string | null) => {
    if (!fecha) return '-';
    return new Date(fecha).toLocaleDateString('es-BO', { 
        day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' 
    });
};

const anularVenta = () => {
    if (confirm('¿Estás seguro de anular esta venta? Se restaurará el stock.')) {
        router.post('/ventas/' + props.venta.id + '/anular');
    }
};

const totalPagado = () => props.venta.pagos.filter(p => p.estado === 'completado').reduce((sum, p) => sum + Number(p.monto), 0);
const saldoPendiente = () => Number(props.venta.total) - totalPagado();

const qrSrc = () => {
    if (!props.qrImage) return '';
    return props.qrImage.startsWith('data:') ? props.qrImage : 'data:image/png;base64,' + props.qrImage;
};
</script>

<template>
    <Head :title="'Venta ' + venta.numero_venta" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center justify-between max-w-5xl mx-auto">
                <div class="flex items-center space-x-4">
                    <Link href="/ventas" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">← Volver</Link>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Venta {{ venta.numero_venta }}</h1>
                </div>
                <span :class="estadoColor(venta.estado)" class="px-3 py-1 rounded-full text-sm font-medium capitalize">
                    {{ venta.estado }}
                </span>
            </div>
        </nav>

        <div class="max-w-5xl mx-auto py-6 px-4 space-y-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Fecha</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ formatFecha(venta.created_at) }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Vendedor</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ venta.vendedor?.name }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Cliente</div>
                        <div class="font-medium text-gray-900 dark:text-white">{{ venta.cliente?.name || 'Público General' }}</div>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">Tipo</div>
                        <div class="font-medium text-gray-900 dark:text-white capitalize">{{ venta.tipo }}</div>
                    </div>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b dark:border-gray-700">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Detalle de Productos</h2>
                </div>
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Producto</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cantidad</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Precio</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="detalle in venta.detalles" :key="detalle.id">
                            <td class="px-6 py-4">
                                <div class="font-medium text-gray-900 dark:text-white">{{ detalle.producto.nombre }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">{{ detalle.producto.codigo }}</div>
                            </td>
                            <td class="px-6 py-4 text-right text-gray-900 dark:text-white">{{ detalle.cantidad }}</td>
                            <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">Bs. {{ detalle.precio_unitario }}</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Bs. {{ detalle.subtotal }}</td>
                        </tr>
                    </tbody>
                    <tfoot class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <td colspan="3" class="px-6 py-3 text-right text-lg font-bold text-gray-900 dark:text-white">TOTAL:</td>
                            <td class="px-6 py-3 text-right text-lg font-bold text-gray-900 dark:text-white">Bs. {{ venta.total }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <div v-if="qrImage && tienePagoPendiente()" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 text-center">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">📱 Escanea para Pagar</h2>
                <img :src="qrSrc()" alt="Código QR de Pago" class="mx-auto w-64 h-64 rounded-lg" />
                <p class="mt-4 text-gray-600 dark:text-gray-400">Escanea el código QR con tu app de banco</p>
                <p class="mt-2 text-sm text-blue-600 animate-pulse">⏳ Esperando confirmación de pago...</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <div class="px-6 py-4 border-b dark:border-gray-700 flex justify-between items-center">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white">Pagos</h2>
                    <div class="text-sm">
                        <span class="text-gray-500 dark:text-gray-400">Saldo: </span>
                        <span :class="saldoPendiente() > 0 ? 'text-red-600 font-bold' : 'text-green-600 font-bold'">
                            Bs. {{ saldoPendiente().toFixed(2) }}
                        </span>
                    </div>
                </div>
                <div v-if="venta.pagos.length === 0" class="px-6 py-8 text-center text-gray-500">
                    No hay pagos registrados
                </div>
                <table v-else class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Método</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Monto</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="pago in venta.pagos" :key="pago.id">
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ formatFecha(pago.fecha_pago) }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white capitalize">{{ pago.metodo }}</td>
                            <td class="px-6 py-4 text-right font-medium text-gray-900 dark:text-white">Bs. {{ pago.monto }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="estadoPagoColor(pago.estado)" class="px-2 py-1 text-xs rounded-full">
                                    {{ estadoPagoTexto(pago.estado) }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="venta.estado !== 'cancelado'" class="flex justify-end">
                <button @click="anularVenta" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                    Anular Venta
                </button>
            </div>
        </div>
    </div>
</template>
