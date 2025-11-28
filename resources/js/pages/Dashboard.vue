<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps<{
    auth: { user: { name: string; rol: string } };
}>();
</script>

<template>
    <Head title="Dashboard" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <!-- Navbar -->
        <nav class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex items-center">
                        <span class="text-xl font-bold text-gray-900 dark:text-white">Tienda Dame</span>
                    </div>
                    <div class="flex items-center space-x-4">
                        <span class="text-gray-600 dark:text-gray-300">{{ auth.user.name }}</span>
                        <Link href="/logout" method="post" as="button" class="text-red-600 hover:text-red-800">
                            Salir
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-8">Panel de Control</h1>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                    <!-- Productos -->
                    <Link href="/productos" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center">
                            <div class="p-3 bg-blue-100 dark:bg-blue-900 rounded-full">
                                <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Productos</h2>
                                <p class="text-gray-600 dark:text-gray-400">Gestionar inventario</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Categorías -->
                    <Link href="/categorias" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center">
                            <div class="p-3 bg-green-100 dark:bg-green-900 rounded-full">
                                <svg class="w-8 h-8 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Categorías</h2>
                                <p class="text-gray-600 dark:text-gray-400">Organizar productos</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Nueva Venta -->
                    <Link href="/ventas/create" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center">
                            <div class="p-3 bg-purple-100 dark:bg-purple-900 rounded-full">
                                <svg class="w-8 h-8 text-purple-600 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Nueva Venta</h2>
                                <p class="text-gray-600 dark:text-gray-400">Registrar venta</p>
                            </div>
                        </div>
                    </Link>

                    <!-- Ventas -->
                    <Link href="/ventas" class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-6 hover:shadow-lg transition">
                        <div class="flex items-center">
                            <div class="p-3 bg-orange-100 dark:bg-orange-900 rounded-full">
                                <svg class="w-8 h-8 text-orange-600 dark:text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                            </div>
                            <div class="ml-4">
                                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Ventas</h2>
                                <p class="text-gray-600 dark:text-gray-400">Historial de ventas</p>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </div>
</template>
