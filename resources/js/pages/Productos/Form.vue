<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

interface Categoria {
    id: number;
    nombre: string;
}

interface Producto {
    id?: number;
    codigo: string;
    nombre: string;
    descripcion: string;
    categoria_id: number | null;
    precio_compra: number;
    precio_venta: number;
    stock: number;
    stock_minimo: number;
    unidad_medida: string;
    activo: boolean;
}

const props = defineProps<{
    producto?: Producto;
    categorias: Categoria[];
}>();

const form = ref<Producto>({
    codigo: props.producto?.codigo || '',
    nombre: props.producto?.nombre || '',
    descripcion: props.producto?.descripcion || '',
    categoria_id: props.producto?.categoria_id || null,
    precio_compra: props.producto?.precio_compra || 0,
    precio_venta: props.producto?.precio_venta || 0,
    stock: props.producto?.stock || 0,
    stock_minimo: props.producto?.stock_minimo || 5,
    unidad_medida: props.producto?.unidad_medida || 'unidad',
    activo: props.producto?.activo ?? true,
});

const guardar = () => {
    if (props.producto?.id) {
        router.put(`/productos/${props.producto.id}`, form.value);
    } else {
        router.post('/productos', form.value);
    }
};
</script>

<template>
    <Head :title="producto ? 'Editar Producto' : 'Nuevo Producto'" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center space-x-4 max-w-3xl mx-auto">
                <Link href="/productos" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">← Volver</Link>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">
                    {{ producto ? 'Editar Producto' : 'Nuevo Producto' }}
                </h1>
            </div>
        </nav>

        <div class="max-w-3xl mx-auto py-6 px-4">
            <form @submit.prevent="guardar" class="bg-white dark:bg-gray-800 rounded-lg shadow p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Código *</label>
                        <input v-model="form.codigo" type="text" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nombre *</label>
                        <input v-model="form.nombre" type="text" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Descripción</label>
                    <textarea v-model="form.descripcion" rows="3" 
                        class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Categoría *</label>
                        <select v-model="form.categoria_id" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option :value="null" disabled>Seleccionar...</option>
                            <option v-for="cat in categorias" :key="cat.id" :value="cat.id">{{ cat.nombre }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Unidad de Medida</label>
                        <select v-model="form.unidad_medida" 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                            <option value="unidad">Unidad</option>
                            <option value="kg">Kilogramo</option>
                            <option value="litro">Litro</option>
                            <option value="metro">Metro</option>
                            <option value="caja">Caja</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Compra (Bs.) *</label>
                        <input v-model.number="form.precio_compra" type="number" step="0.01" min="0" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Precio Venta (Bs.) *</label>
                        <input v-model.number="form.precio_venta" type="number" step="0.01" min="0" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Actual *</label>
                        <input v-model.number="form.stock" type="number" min="0" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Stock Mínimo *</label>
                        <input v-model.number="form.stock_minimo" type="number" min="0" required 
                            class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>
                </div>

                <div v-if="producto" class="flex items-center">
                    <input v-model="form.activo" type="checkbox" id="activo" class="mr-2" />
                    <label for="activo" class="text-sm text-gray-700 dark:text-gray-300">Producto activo</label>
                </div>

                <div class="flex justify-end space-x-3 pt-4 border-t dark:border-gray-700">
                    <Link href="/productos" class="px-4 py-2 text-gray-600 hover:text-gray-800">Cancelar</Link>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        {{ producto ? 'Actualizar' : 'Crear Producto' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
