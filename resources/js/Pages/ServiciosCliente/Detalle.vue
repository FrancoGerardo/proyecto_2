<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    servicio: Object,
    medicos: Array,
});

function formatearMoneda(valor) {
    if (!valor) return 'Bs 0.00';
    return new Intl.NumberFormat('es-BO', { 
        style: 'currency', 
        currency: 'BOB' 
    }).format(Number(valor));
}

function obtenerNombreMedico(medico) {
    return medico?.usuario?.persona?.nombre_completo || 'Médico sin nombre';
}
</script>

<template>
    <AppLayout :title="servicio?.nombre || 'Detalle del Servicio'">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Detalle del Servicio
                </h2>
                <Link 
                    :href="route('cliente.servicios.index')"
                    class="text-indigo-600 hover:text-indigo-800 font-medium"
                >
                    ← Volver a Servicios
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Información del Servicio -->
                    <div class="lg:col-span-2">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-6">
                                <h1 class="text-3xl font-bold text-white mb-2">{{ servicio.nombre }}</h1>
                                <span class="inline-block px-3 py-1 bg-white bg-opacity-20 rounded-full text-sm text-white">
                                    {{ servicio.categoria }}
                                </span>
                            </div>

                            <div class="p-6">
                                <div class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Descripción</h3>
                                    <p class="text-gray-600">
                                        {{ servicio.descripcion || 'Sin descripción disponible' }}
                                    </p>
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-6">
                                    <div class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 mb-1">Precio</p>
                                        <p class="text-2xl font-bold text-indigo-600">
                                            {{ formatearMoneda(servicio.costo) }}
                                        </p>
                                    </div>
                                    <div v-if="servicio.duracion_minutos" class="bg-gray-50 p-4 rounded-lg">
                                        <p class="text-sm text-gray-500 mb-1">Duración</p>
                                        <p class="text-2xl font-bold text-gray-700">
                                            {{ servicio.duracion_minutos }} min
                                        </p>
                                    </div>
                                </div>

                                <div v-if="servicio.especialidad" class="mb-6">
                                    <h3 class="text-lg font-semibold text-gray-800 mb-2">Especialidad</h3>
                                    <p class="text-gray-600">{{ servicio.especialidad.nombre }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Médicos -->
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden">
                            <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-4">
                                <h3 class="text-xl font-bold text-white">Médicos Disponibles</h3>
                            </div>

                            <div class="p-4">
                                <div v-if="medicos.length === 0" class="text-center py-8">
                                    <p class="text-gray-500">No hay médicos disponibles para este servicio.</p>
                                </div>

                                <div v-else class="space-y-3">
                                    <div
                                        v-for="medico in medicos"
                                        :key="medico.usuario_id"
                                        class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 transition"
                                    >
                                        <h4 class="font-semibold text-gray-800 mb-2">
                                            {{ obtenerNombreMedico(medico) }}
                                        </h4>
                                        <div v-if="medico.especialidades && medico.especialidades.length > 0" class="mt-2">
                                            <p class="text-xs text-gray-500 mb-1">Especialidades:</p>
                                            <div class="flex flex-wrap gap-1">
                                                <span
                                                    v-for="esp in medico.especialidades"
                                                    :key="esp.id"
                                                    class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"
                                                >
                                                    {{ esp.nombre }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <Link
                                        :href="route('cliente.fichas.create', { servicio_id: servicio.id })"
                                        class="block w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg text-center transition"
                                    >
                                        Agendar Cita
                                    </Link>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

