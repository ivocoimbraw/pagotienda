<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';

interface Producto {
    id: number;
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
    metodo_pago: string;
    estado: number;
    fecha_pago: string;
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

interface Pagination {
    data: Venta[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
}

defineProps<{
    ventas: Pagination;
}>();

const estadoColor = (estado: string) => {
    switch (estado) {
        case 'pagada': return 'bg-green-100 text-green-800';
        case 'pendiente': return 'bg-yellow-100 text-yellow-800';
        case 'cancelada': return 'bg-red-100 text-red-800';
        default: return 'bg-gray-100 text-gray-800';
    }
};

const formatFecha = (fecha: string) => new Date(fecha).toLocaleDateString('es-BO', { 
    day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' 
});
</script>

<template>
    <Head title="Ventas" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <div class="flex items-center space-x-4">
                    <Link href="/dashboard" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">← Volver</Link>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Ventas</h1>
                </div>
                <Link href="/ventas/create" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700">
                    + Nueva Venta
                </Link>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">N° Venta</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vendedor</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Cliente</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Total</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="venta in ventas.data" :key="venta.id">
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-mono font-bold">{{ venta.numero_venta }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ formatFecha(venta.created_at) }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ venta.vendedor?.name }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ venta.cliente?.name || 'Público General' }}</td>
                            <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-bold">Bs. {{ venta.total }}</td>
                            <td class="px-6 py-4 text-center">
                                <span :class="estadoColor(venta.estado)" class="px-2 py-1 text-xs rounded-full capitalize">
                                    {{ venta.estado }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <Link :href="`/ventas/${venta.id}`" class="text-blue-600 hover:text-blue-800">Ver</Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4 flex justify-center">
                <nav class="flex space-x-1">
                    <template v-for="link in ventas.links" :key="link.label">
                        <Link v-if="link.url" :href="link.url" 
                            :class="link.active ? 'bg-blue-600 text-white' : 'bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-300'"
                            class="px-3 py-2 rounded border dark:border-gray-700" v-html="link.label" />
                        <span v-else class="px-3 py-2 text-gray-400" v-html="link.label" />
                    </template>
                </nav>
            </div>
        </div>
    </div>
</template>
