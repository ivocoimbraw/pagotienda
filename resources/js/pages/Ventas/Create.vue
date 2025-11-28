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
    precio_venta: number;
    stock: number;
    categoria: Categoria;
}

interface ItemCarrito {
    producto: Producto;
    cantidad: number;
    precio: number;
    descuento: number;
}

interface Usuario {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    productos: Producto[];
    clientes: Usuario[];
}>();

const carrito = ref<ItemCarrito[]>([]);
const busqueda = ref('');
const clienteId = ref<number | null>(null);
const metodoPago = ref('efectivo');
const observaciones = ref('');

const productosFiltrados = computed(() => {
    if (!busqueda.value) return props.productos.slice(0, 20);
    const q = busqueda.value.toLowerCase();
    return props.productos.filter(p => 
        p.codigo.toLowerCase().includes(q) || p.nombre.toLowerCase().includes(q)
    ).slice(0, 20);
});

const subtotal = computed(() => carrito.value.reduce((sum, item) => sum + (item.precio * item.cantidad), 0));
const totalDescuento = computed(() => carrito.value.reduce((sum, item) => sum + item.descuento, 0));
const total = computed(() => subtotal.value - totalDescuento.value);

const agregarProducto = (producto: Producto) => {
    const existente = carrito.value.find(item => item.producto.id === producto.id);
    if (existente) {
        if (existente.cantidad < producto.stock) {
            existente.cantidad++;
        } else {
            alert('Stock insuficiente');
        }
    } else {
        if (producto.stock > 0) {
            carrito.value.push({ producto, cantidad: 1, precio: producto.precio_venta, descuento: 0 });
        } else {
            alert('Producto sin stock');
        }
    }
};

const quitarProducto = (index: number) => {
    carrito.value.splice(index, 1);
};

const actualizarCantidad = (index: number, cantidad: number) => {
    const item = carrito.value[index];
    if (cantidad > item.producto.stock) {
        alert('Stock insuficiente');
        item.cantidad = item.producto.stock;
    } else if (cantidad < 1) {
        quitarProducto(index);
    } else {
        item.cantidad = cantidad;
    }
};

const procesarVenta = () => {
    if (carrito.value.length === 0) {
        alert('Agregue productos al carrito');
        return;
    }

    router.post('/ventas', {
        cliente_id: clienteId.value,
        tipo: 'contado',
        metodo_pago: metodoPago.value,
        observaciones: observaciones.value,
        detalles: carrito.value.map(item => ({
            producto_id: item.producto.id,
            cantidad: item.cantidad,
            precio_unitario: item.precio,
            descuento: item.descuento,
        })),
    });
};
</script>

<template>
    <Head title="Nueva Venta" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-6 py-4">
            <div class="flex items-center space-x-4 max-w-7xl mx-auto">
                <Link href="/ventas" class="text-gray-600 dark:text-gray-300 hover:text-gray-900">← Volver</Link>
                <h1 class="text-xl font-bold text-gray-900 dark:text-white">Nueva Venta</h1>
            </div>
        </nav>

        <div class="max-w-7xl mx-auto py-6 px-4">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Panel de Productos -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <input v-model="busqueda" type="text" placeholder="Buscar producto por código o nombre..." 
                            class="w-full px-4 py-3 border rounded-lg text-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Productos Disponibles</h2>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-3">
                            <button v-for="producto in productosFiltrados" :key="producto.id" 
                                @click="agregarProducto(producto)"
                                :disabled="producto.stock === 0"
                                :class="producto.stock === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:border-blue-500'"
                                class="p-3 border rounded-lg text-left dark:border-gray-700 transition">
                                <div class="text-xs text-gray-500 dark:text-gray-400 font-mono">{{ producto.codigo }}</div>
                                <div class="font-medium text-gray-900 dark:text-white truncate">{{ producto.nombre }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">Stock: {{ producto.stock }}</div>
                                <div class="text-lg font-bold text-green-600">Bs. {{ producto.precio_venta }}</div>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Panel del Carrito -->
                <div class="space-y-4">
                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">🛒 Carrito</h2>
                        
                        <div v-if="carrito.length === 0" class="text-center py-8 text-gray-500 dark:text-gray-400">
                            Carrito vacío
                        </div>

                        <div v-else class="space-y-3">
                            <div v-for="(item, index) in carrito" :key="item.producto.id" 
                                class="border-b dark:border-gray-700 pb-3">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ item.producto.nombre }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">Bs. {{ item.precio }} c/u</div>
                                    </div>
                                    <button @click="quitarProducto(index)" class="text-red-500 hover:text-red-700">✕</button>
                                </div>
                                <div class="flex items-center mt-2 space-x-2">
                                    <button @click="actualizarCantidad(index, item.cantidad - 1)" 
                                        class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded">-</button>
                                    <input :value="item.cantidad" @change="actualizarCantidad(index, +($event.target as HTMLInputElement).value)" 
                                        type="number" min="1" class="w-16 text-center border rounded dark:bg-gray-700 dark:border-gray-600 dark:text-white" />
                                    <button @click="actualizarCantidad(index, item.cantidad + 1)" 
                                        class="w-8 h-8 bg-gray-200 dark:bg-gray-700 rounded">+</button>
                                    <span class="ml-auto font-bold text-gray-900 dark:text-white">
                                        Bs. {{ (item.precio * item.cantidad - item.descuento).toFixed(2) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Cliente</label>
                            <select v-model="clienteId" 
                                class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option :value="null">Público General</option>
                                <option v-for="cliente in clientes" :key="cliente.id" :value="cliente.id">{{ cliente.name }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Método de Pago</label>
                            <select v-model="metodoPago" 
                                class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white">
                                <option value="efectivo">💵 Efectivo</option>
                                <option value="qr">📱 QR (PagoFácil)</option>
                                <option value="transferencia">🏦 Transferencia</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Observaciones</label>
                            <textarea v-model="observaciones" rows="2" 
                                class="mt-1 w-full px-3 py-2 border rounded-lg dark:bg-gray-700 dark:border-gray-600 dark:text-white"></textarea>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4">
                        <div class="flex justify-between text-gray-600 dark:text-gray-400">
                            <span>Subtotal:</span>
                            <span>Bs. {{ subtotal.toFixed(2) }}</span>
                        </div>
                        <div v-if="totalDescuento > 0" class="flex justify-between text-red-600">
                            <span>Descuento:</span>
                            <span>-Bs. {{ totalDescuento.toFixed(2) }}</span>
                        </div>
                        <div class="flex justify-between text-xl font-bold text-gray-900 dark:text-white border-t dark:border-gray-700 pt-2 mt-2">
                            <span>TOTAL:</span>
                            <span>Bs. {{ total.toFixed(2) }}</span>
                        </div>
                    </div>

                    <button @click="procesarVenta" :disabled="carrito.length === 0"
                        :class="carrito.length === 0 ? 'opacity-50 cursor-not-allowed' : 'hover:bg-green-700'"
                        class="w-full py-4 bg-green-600 text-white text-lg font-bold rounded-lg">
                        💳 Procesar Venta
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
