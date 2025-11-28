<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const formulario = useForm({
    email: '',
    password: '',
    remember: false,
});

const enviarLogin = () => {
    formulario.transform(datos => ({
        ...datos,
        remember: formulario.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => formulario.reset('password'),
    });
};
</script>

<template>
    <Head title="Iniciar Sesión - Clínica Santiago Apóstol" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-blue-50 to-purple-50 flex flex-col sm:justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo y Título -->
        <div class="text-center mb-8">
            <Link :href="route('welcome')" class="inline-flex items-center justify-center mb-4">
                <ApplicationMark class="h-16 w-16 text-indigo-600" />
            </Link>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                Iniciar Sesión
            </h1>
            <p class="text-gray-600">
                Accede a tu cuenta de gestión médica
            </p>
        </div>

        <!-- Tarjeta de Login -->
        <div class="w-full sm:max-w-md bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header con gradiente -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white text-center">
                    Bienvenido de Nuevo
                </h2>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="enviarLogin" class="px-6 py-8 space-y-6">
                <!-- Mensaje de estado -->
                <div v-if="status" class="bg-green-50 border border-green-200 rounded-lg p-4">
                    <p class="text-sm font-medium text-green-800">
                        {{ status }}
                    </p>
                </div>

                <!-- Email -->
                <div>
                    <InputLabel for="email" value="Correo Electrónico *" class="text-gray-700 font-medium" />
                    <TextInput
                        id="email"
                        v-model="formulario.email"
                        type="email"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autofocus
                        autocomplete="username"
                        placeholder="tu@email.com"
                    />
                    <InputError class="mt-2" :message="formulario.errors.email" />
                </div>

                <!-- Contraseña -->
                <div>
                    <InputLabel for="password" value="Contraseña *" class="text-gray-700 font-medium" />
                    <TextInput
                        id="password"
                        v-model="formulario.password"
                        type="password"
                        class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        required
                        autocomplete="current-password"
                        placeholder="Ingresa tu contraseña"
                    />
                    <InputError class="mt-2" :message="formulario.errors.password" />
                </div>

                <!-- Recordar sesión y recuperar contraseña -->
                <div class="flex items-center justify-between">
                    <label class="flex items-center">
                        <Checkbox v-model:checked="formulario.remember" name="remember" />
                        <span class="ms-2 text-sm text-gray-600">Recordar sesión</span>
                    </label>

                    <Link 
                        v-if="canResetPassword" 
                        :href="route('password.request')" 
                        class="text-sm text-indigo-600 hover:text-indigo-800 font-medium"
                    >
                        ¿Olvidaste tu contraseña?
                    </Link>
                </div>

                <!-- Botón de Login -->
                <div class="pt-4">
                    <PrimaryButton 
                        class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-lg shadow-lg transition-all duration-200 transform hover:scale-105" 
                        :class="{ 'opacity-50 cursor-not-allowed': formulario.processing }" 
                        :disabled="formulario.processing"
                    >
                        <span v-if="formulario.processing" class="flex items-center justify-center">
                            <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Iniciando sesión...
                        </span>
                        <span v-else>
                            Iniciar Sesión
                        </span>
                    </PrimaryButton>
                </div>

                <!-- Enlace a Registro -->
                <div class="text-center pt-4 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        ¿No tienes una cuenta?
                        <Link 
                            :href="route('register')" 
                            class="text-indigo-600 hover:text-indigo-800 font-semibold ml-1"
                        >
                            Regístrate aquí
                        </Link>
                    </p>
                </div>
            </form>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 text-center">
            <Link 
                :href="route('welcome')" 
                class="text-sm text-indigo-600 hover:text-indigo-800 inline-block"
            >
                ← Volver al inicio
            </Link>
        </div>
    </div>
</template>
