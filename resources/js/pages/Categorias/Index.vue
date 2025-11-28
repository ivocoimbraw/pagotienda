<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Categoria {
    id: number;
    nombre: string;
    descripcion: string | null;
    activo: boolean;
    productos_count: number;
}

defineProps<{
    categorias: Categoria[];
}>();

const showModal = ref(false);
const editando = ref<Categoria | null>(null);
const form = ref({
    nombre: '',
    descripcion: '',
});

const abrirModal = (categoria?: Categoria) => {
    if (categoria) {
        editando.value = categoria;
        form.value = { nombre: categoria.nombre, descripcion: categoria.descripcion || '' };
    } else {
        editando.value = null;
        form.value = { nombre: '', descripcion: '' };
    }
    showModal.value = true;
};

const guardar = () => {
    if (editando.value) {
        router.put(`/categorias/${editando.value.id}`, form.value, {
            onSuccess: () => (showModal.value = false),
        });
    } else {
        router.post('/categorias', form.value, {
            onSuccess: () => (showModal.value = false),
        });
    }
};

const eliminar = (id: number) => {
    if (confirm('¿Estás seguro de eliminar esta categoría?')) {
        router.delete(`/categorias/${id}`);
    }
};
</script>

<template>
    <Head title="Categorías" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center justify-between max-w-7xl mx-auto">
                <div class="flex items-center space-x-4">
                    <Link href="/dashboard" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">
                        ← Volver
                    </Link>
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white">Categorías</h1>
                </div>
                <button @click="abrirModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">
                    + Nueva Categoría
                </button>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 px-4">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Nombre</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Descripción</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Productos</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Estado</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        <tr v-for="categoria in categorias" :key="categoria.id">
                            <td class="px-6 py-4 text-gray-900 dark:text-white font-medium">{{ categoria.nombre }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ categoria.descripcion || '-' }}</td>
                            <td class="px-6 py-4 text-gray-600 dark:text-gray-400">{{ categoria.productos_count }}</td>
                            <td class="px-6 py-4">
                                <span :class="categoria.activo ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'" class="px-2 py-1 text-xs rounded-full">
                                    {{ categoria.activo ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-right space-x-2">
                                <button @click="abrirModal(categoria)" class="text-blue-600 hover:text-blue-800">Editar</button>
                                <button @click="eliminar(categoria.id)" class="text-red-600 hover:text-red-800">Eliminar</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Modal -->
        <div v-if="showModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-md">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-4">
                    {{ editando ? 'Editar Categoría' : 'Nueva Categoría' }}
                </h2>
                <form @submit.prevent="guardar" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre</label>
                        <input v-model="form.nombre" type="text" required class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                        <textarea v-model="form.descripcion" rows="3" class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" @click="showModal = false" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
