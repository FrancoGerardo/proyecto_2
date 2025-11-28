<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';

defineProps({
    fichas: Object,
});

function getEstadoClass(estado) {
    const clases = {
        'PENDIENTE': 'bg-yellow-100 text-yellow-800',
        'CONFIRMADA': 'bg-blue-100 text-blue-800',
        'ATENDIDA': 'bg-green-100 text-green-800',
        'CANCELADA': 'bg-red-100 text-red-800',
    };
    return clases[estado] || 'bg-gray-100 text-gray-800';
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
</script>

<template>
    <AppLayout title="Mis Fichas">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    Mis Fichas
                </h2>
                <Link
                    :href="route('cliente.fichas.create')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                >
                    + Generar Nueva Ficha
                </Link>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div v-if="fichas.data && fichas.data.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="ficha in fichas.data"
                        :key="ficha.id"
                        class="bg-white rounded-lg shadow-md hover:shadow-xl transition overflow-hidden"
                    >
                        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 p-4">
                            <h3 class="text-lg font-bold text-white">{{ ficha.servicio?.nombre || 'Sin servicio' }}</h3>
                            <span :class="['inline-block mt-2 px-3 py-1 rounded-full text-xs font-semibold', getEstadoClass(ficha.estado)]">
                                {{ ficha.estado }}
                            </span>
                        </div>

                        <div class="p-6">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-500 text-sm">Médico:</span>
                                    <p class="font-semibold text-gray-800">
                                        {{ ficha.medico?.usuario?.persona?.nombre_completo || 'Sin médico' }}
                                    </p>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-sm">Fecha:</span>
                                    <p class="font-semibold text-gray-800">{{ formatearFecha(ficha.fecha) }}</p>
                                </div>
                                <div>
                                    <span class="text-gray-500 text-sm">Hora:</span>
                                    <p class="font-semibold text-gray-800">{{ ficha.hora }}</p>
                                </div>
                                <div v-if="ficha.motivo_consulta">
                                    <span class="text-gray-500 text-sm">Motivo:</span>
                                    <p class="text-gray-800 text-sm mt-1">{{ ficha.motivo_consulta }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="text-center py-12">
                    <p class="text-gray-500 text-lg mb-4">No tienes fichas registradas.</p>
                    <Link
                        :href="route('cliente.fichas.create')"
                        class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-lg transition"
                    >
                        Generar Primera Ficha
                    </Link>
                </div>

                <!-- Paginación -->
                <div v-if="fichas.links && fichas.links.length > 3" class="mt-6 flex justify-center">
                    <div class="flex space-x-2">
                        <Link
                            v-for="link in fichas.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            :class="['px-4 py-2 rounded-lg', link.active ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100']"
                            v-html="link.label"
                        ></Link>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

