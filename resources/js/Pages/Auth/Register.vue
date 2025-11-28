<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import ApplicationMark from '@/Components/ApplicationMark.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const formulario = useForm({
    dni: '',
    nombre: '',
    apellidos: '',
    telefono: '',
    direccion: '',
    fecha_nacimiento: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const enviarRegistro = () => {
    formulario.post(route('register'), {
        onFinish: () => formulario.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Registro - Clínica Santiago Apóstol" />

    <div class="min-h-screen bg-gradient-to-br from-indigo-50 via-blue-50 to-purple-50 flex flex-col sm:justify-center items-center py-12 px-4 sm:px-6 lg:px-8">
        <!-- Logo y Título -->
        <div class="text-center mb-8">
            <Link :href="route('welcome')" class="inline-flex items-center justify-center mb-4">
                <ApplicationMark class="h-16 w-16 text-indigo-600" />
            </Link>
            <h1 class="text-3xl md:text-4xl font-bold text-gray-900 mb-2">
                Crear Cuenta
            </h1>
            <p class="text-gray-600">
                Únete a nuestra plataforma de gestión médica
            </p>
        </div>

        <!-- Tarjeta de Registro -->
        <div class="w-full sm:max-w-2xl bg-white rounded-2xl shadow-xl overflow-hidden">
            <!-- Header con gradiente -->
            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 px-6 py-4">
                <h2 class="text-xl font-semibold text-white text-center">
                    Registro de Cliente
                </h2>
            </div>

            <!-- Formulario -->
            <form @submit.prevent="enviarRegistro" class="px-6 py-8">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- DNI -->
                    <div>
                        <InputLabel for="dni" value="DNI *" class="text-gray-700 font-medium" />
                        <TextInput
                            id="dni"
                            v-model="formulario.dni"
                            type="text"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            autofocus
                            placeholder="12345678"
                        />
                        <InputError class="mt-2" :message="formulario.errors.dni" />
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
                            autocomplete="username"
                            placeholder="tu@email.com"
                        />
                        <InputError class="mt-2" :message="formulario.errors.email" />
                    </div>

                    <!-- Nombre -->
                    <div>
                        <InputLabel for="nombre" value="Nombre *" class="text-gray-700 font-medium" />
                        <TextInput
                            id="nombre"
                            v-model="formulario.nombre"
                            type="text"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="Ingresa tu nombre"
                        />
                        <InputError class="mt-2" :message="formulario.errors.nombre" />
                    </div>

                    <!-- Apellidos -->
                    <div>
                        <InputLabel for="apellidos" value="Apellidos *" class="text-gray-700 font-medium" />
                        <TextInput
                            id="apellidos"
                            v-model="formulario.apellidos"
                            type="text"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            placeholder="Ingresa tus apellidos"
                        />
                        <InputError class="mt-2" :message="formulario.errors.apellidos" />
                    </div>

                    <!-- Teléfono -->
                    <div>
                        <InputLabel for="telefono" value="Teléfono" class="text-gray-700 font-medium" />
                        <TextInput
                            id="telefono"
                            v-model="formulario.telefono"
                            type="tel"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="70000000"
                        />
                        <InputError class="mt-2" :message="formulario.errors.telefono" />
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div>
                        <InputLabel for="fecha_nacimiento" value="Fecha de Nacimiento" class="text-gray-700 font-medium" />
                        <TextInput
                            id="fecha_nacimiento"
                            v-model="formulario.fecha_nacimiento"
                            type="date"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                        <InputError class="mt-2" :message="formulario.errors.fecha_nacimiento" />
                    </div>

                    <!-- Dirección -->
                    <div class="md:col-span-2">
                        <InputLabel for="direccion" value="Dirección" class="text-gray-700 font-medium" />
                        <TextInput
                            id="direccion"
                            v-model="formulario.direccion"
                            type="text"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Ingresa tu dirección"
                        />
                        <InputError class="mt-2" :message="formulario.errors.direccion" />
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
                            autocomplete="new-password"
                            placeholder="Mínimo 8 caracteres"
                        />
                        <InputError class="mt-2" :message="formulario.errors.password" />
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <InputLabel for="password_confirmation" value="Confirmar Contraseña *" class="text-gray-700 font-medium" />
                        <TextInput
                            id="password_confirmation"
                            v-model="formulario.password_confirmation"
                            type="password"
                            class="mt-2 block w-full rounded-lg border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            required
                            autocomplete="new-password"
                            placeholder="Repite tu contraseña"
                        />
                        <InputError class="mt-2" :message="formulario.errors.password_confirmation" />
                    </div>
                </div>

                <!-- Términos y Condiciones -->
                <div v-if="$page.props.jetstream.hasTermsAndPrivacyPolicyFeature" class="mt-6 bg-gray-50 rounded-lg p-4">
                    <InputLabel for="terms">
                        <div class="flex items-start">
                            <Checkbox 
                                id="terms" 
                                v-model:checked="formulario.terms" 
                                name="terms" 
                                required 
                                class="mt-1"
                            />
                            <div class="ms-3 text-sm text-gray-600">
                                Acepto los 
                                <a 
                                    target="_blank" 
                                    :href="route('terms.show')" 
                                    class="text-indigo-600 hover:text-indigo-800 font-medium underline"
                                >
                                    Términos de Servicio
                                </a> 
                                y la 
                                <a 
                                    target="_blank" 
                                    :href="route('policy.show')" 
                                    class="text-indigo-600 hover:text-indigo-800 font-medium underline"
                                >
                                    Política de Privacidad
                                </a>
                            </div>
                        </div>
                        <InputError class="mt-2" :message="formulario.errors.terms" />
                    </InputLabel>
                </div>

                <!-- Botón de Registro -->
                <div class="mt-6">
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
                            Registrando...
                        </span>
                        <span v-else>
                            Crear Cuenta
                        </span>
                    </PrimaryButton>
                </div>

                <!-- Enlace a Login -->
                <div class="text-center mt-6 pt-6 border-t border-gray-200">
                    <p class="text-sm text-gray-600">
                        ¿Ya tienes una cuenta?
                        <Link 
                            :href="route('login')" 
                            class="text-indigo-600 hover:text-indigo-800 font-semibold ml-1"
                        >
                            Inicia Sesión
                        </Link>
                    </p>
                </div>
            </form>
        </div>

        <!-- Información adicional -->
        <div class="mt-8 text-center">
            <p class="text-sm text-gray-500">
                Al registrarte, aceptas nuestros términos y condiciones de uso
            </p>
            <Link 
                :href="route('welcome')" 
                class="text-sm text-indigo-600 hover:text-indigo-800 mt-2 inline-block"
            >
                ← Volver al inicio
            </Link>
        </div>
    </div>
</template>
