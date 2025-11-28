<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    servicios: Array,
});

function formatearMoneda(valor) {
    if (!valor) return 'Bs 0.00';
    return new Intl.NumberFormat('es-BO', { 
        style: 'currency', 
        currency: 'BOB' 
    }).format(Number(valor));
}

function obtenerColorCategoria(categoria) {
    const colores = {
        'ESPECIALIDAD': 'bg-blue-100 text-blue-800',
        'INTERNACION': 'bg-green-100 text-green-800',
        'ENFERMERIA': 'bg-purple-100 text-purple-800',
    };
    return colores[categoria] || 'bg-gray-100 text-gray-800';
}
</script>

<template>
    <AppLayout title="Servicios Disponibles">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Servicios Disponibles
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Grid de Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="servicio in servicios"
                        :key="servicio.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-300 overflow-hidden cursor-pointer"
                        @click="$inertia.visit(route('cliente.servicios.show', servicio.id))"
                    >
                        <!-- Header de la Card -->
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4">
                            <h3 class="text-xl font-bold text-white">{{ servicio.nombre }}</h3>
                            <span :class="['inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold', obtenerColorCategoria(servicio.categoria)]">
                                {{ servicio.categoria }}
                            </span>
                        </div>

                        <!-- Contenido de la Card -->
                        <div class="p-6">
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">
                                {{ servicio.descripcion || 'Sin descripción disponible' }}
                            </p>

                            <div class="space-y-2">
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-500 text-sm">Precio:</span>
                                    <span class="text-2xl font-bold text-indigo-600">
                                        {{ formatearMoneda(servicio.costo) }}
                                    </span>
                                </div>

                                <div v-if="servicio.duracion_minutos" class="flex justify-between items-center">
                                    <span class="text-gray-500 text-sm">Duración:</span>
                                    <span class="text-gray-700 font-medium">
                                        {{ servicio.duracion_minutos }} min
                                    </span>
                                </div>

                                <div v-if="servicio.especialidad" class="flex justify-between items-center">
                                    <span class="text-gray-500 text-sm">Especialidad:</span>
                                    <span class="text-gray-700 font-medium">
                                        {{ servicio.especialidad.nombre }}
                                    </span>
                                </div>
                            </div>

                            <!-- Botón Ver Detalles -->
                            <div class="mt-6">
                                <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition">
                                    Ver Detalles y Médicos
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mensaje si no hay servicios -->
                <div v-if="servicios.length === 0" class="text-center py-12">
                    <p class="text-gray-500 text-lg">No hay servicios disponibles en este momento.</p>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

