<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

interface Categoria {
    id: number;
    nombre: string;
}

interface Producto {
    id: number;
    codigo: string;
    nombre: string;
    categoria: Categoria;
    precio_compra: number;
    precio_venta: number;
    stock: number;
    stock_minimo: number;
    activo: boolean;
}

interface Pagination {
    data: Producto[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: { url: string | null; label: string; active: boolean }[];
}

const props = defineProps<{
    productos: Pagination;
    categorias: Categoria[];
    filtros: { buscar?: string; categoria?: string };
}>();

const buscar = ref(props.filtros.buscar || '');
const categoriaFiltro = ref(props.filtros.categoria || '');

const filtrar = () => {
    router.get('/productos', { buscar: buscar.value, categoria: categoriaFiltro.value }, { preserveState: true });
};

const eliminar = (id: number) => {
    if (confirm('¿Desactivar este producto?')) {
        router.delete(`/productos/${id}`);
    }
};
</script>

<template>
    <Head title="Productos" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <div class="flex items-center space-x-4">
                    <Link href="/dashboard" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">← Volver</Link>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Productos</h1>
                </div>
                <Link href="/productos/create" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    + Nuevo Producto
                </Link>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 px-4">
            <!-- Filtros -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 mb-6">
                <div class="flex flex-wrap gap-4">
                    <input v-model="buscar" @keyup.enter="filtrar" type="text" placeholder="Buscar producto..." 
                        class="flex-1 min-w-[200px] px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    <select v-model="categoriaFiltro" @change="filtrar" 
                        class="px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                        <option value="">Todas las categorías</option>
                        <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                    </select>
                    <button @click="filtrar" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700">Buscar</button>
                </div>
            </div>

            <!-- Tabla -->
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Código</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Categoría</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">P. Compra</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">P. Venta</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Stock</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="producto in productos.data" :key="producto.id" :class="{ 'bg-red-50 dark:bg-red-900/20': producto.stock <= producto.stock_minimo }">
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-mono">{{ producto.codigo }}</td>
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ producto.nombre }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ producto.categoria?.nombre }}</td>
                            <td class="px-6 py-4 text-right text-gray-600 dark:text-gray-400">Bs. {{ producto.precio_compra }}</td>
                            <td class="px-6 py-4 text-right text-gray-900 dark:text-white font-medium">Bs. {{ producto.precio_venta }}</td>
                            <td class="px-6 py-4 text-right">
                                <span :class="producto.stock <= producto.stock_minimo ? 'text-red-600 font-bold' : 'text-gray-900 dark:text-white'">
                                    {{ producto.stock }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                <span :class="producto.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                                    {{ producto.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <Link :href="`/productos/${producto.id}/edit`" class="text-blue-600 hover:text-blue-800">Editar</Link>
                                <button @click="eliminar(producto.id)" class="text-red-600 hover:text-red-800">Desactivar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-4 flex justify-center">
                <nav class="flex space-x-1">
                    <template v-for="link in productos.links" :key="link.label">
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
