<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión" />

    <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900 py-12 px-4">
        <div class="max-w-md w-full space-y-8">
            <div class="text-center">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">🏪 Tienda Dame</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Sistema de Inventarios y Ventas</p>
            </div>

            <div class="bg-white dark:bg-gray-800 rounded-lg shadow-lg p-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-6">Iniciar Sesión</h2>

                <form @submit.prevent="submit" class="space-y-6">
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Correo Electrónico
                        </label>
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            required
                            autofocus
                            class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="correo@ejemplo.com"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Contraseña
                        </label>
                        <input
                            id="password"
                            v-model="form.password"
                            type="password"
                            required
                            class="mt-1 w-full px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                            placeholder="••••••••"
                        />
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-600">{{ form.errors.password }}</p>
                    </div>

                    <div class="flex items-center">
                        <input
                            id="remember"
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500"
                        />
                        <label for="remember" class="ml-2 text-sm text-gray-700 dark:text-gray-300">
                            Recordarme
                        </label>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="w-full py-3 px-4 bg-blue-600 text-white font-semibold rounded-lg hover:bg-blue-700 focus:ring-4 focus:ring-blue-300 disabled:opacity-50 disabled:cursor-not-allowed transition"
                    >
                        <span v-if="form.processing">Ingresando...</span>
                        <span v-else>Ingresar</span>
                    </button>
                </form>
            </div>

            <div class="text-center text-sm text-gray-500 dark:text-gray-400">
                <p>Credenciales de prueba:</p>
                <p class="font-mono">admin@tiendadame.com / password</p>
            </div>
        </div>
    </div>
</template>
