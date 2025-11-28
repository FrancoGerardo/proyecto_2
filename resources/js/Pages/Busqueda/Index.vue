<template>
    <AppLayout title="Búsqueda">
        <template #header>
            <h2 class="tema-titulo">Resultados de Búsqueda</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema mb-6">
                <h3 class="tema-titulo mb-4">Búsqueda: "{{ termino }}"</h3>
                <p class="tema-texto-secundario" v-if="!termino || termino.trim() === ''">
                    Ingrese un término de búsqueda en el campo del header para buscar información.
                </p>
            </div>

            <!-- Resultados de Usuarios -->
            <div v-if="resultados.usuarios && resultados.usuarios.length > 0" class="card-tema mb-6">
                <h3 class="tema-titulo mb-4">Usuarios ({{ resultados.usuarios.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>DNI</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="usuario in resultados.usuarios" :key="usuario.id">
                                <td class="text-center">{{ usuario.id }}</td>
                                <td class="tema-texto font-bold">{{ usuario.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ usuario.email }}</td>
                                <td class="text-center">{{ usuario.persona?.dni || 'N/A' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resultados de Salas -->
            <div v-if="resultados.salas && resultados.salas.length > 0" class="card-tema mb-6">
                <h3 class="tema-titulo mb-4">Salas ({{ resultados.salas.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Número</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sala in resultados.salas" :key="sala.id">
                                <td class="text-center">{{ sala.id }}</td>
                                <td class="tema-texto font-bold">{{ sala.numero }}</td>
                                <td class="text-center">{{ sala.categoria }}</td>
                                <td class="text-center">{{ sala.estado }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resultados de Servicios -->
            <div v-if="resultados.servicios && resultados.servicios.length > 0" class="card-tema mb-6">
                <h3 class="tema-titulo mb-4">Servicios ({{ resultados.servicios.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Categoría</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="servicio in resultados.servicios" :key="servicio.id">
                                <td class="text-center">{{ servicio.id }}</td>
                                <td class="tema-texto font-bold">{{ servicio.nombre }}</td>
                                <td class="text-center">{{ servicio.categoria }}</td>
                                <td class="text-center">Bs. {{ servicio.costo }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Resultados de Fichas -->
            <div v-if="resultados.fichas && resultados.fichas.length > 0" class="card-tema mb-6">
                <h3 class="tema-titulo mb-4">Fichas ({{ resultados.fichas.length }})</h3>
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Servicio</th>
                                <th>Fecha</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ficha in resultados.fichas" :key="ficha.id">
                                <td class="text-center">{{ ficha.id }}</td>
                                <td class="tema-texto font-bold">{{ ficha.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ ficha.servicio?.nombre || 'N/A' }}</td>
                                <td class="text-center">{{ ficha.fecha }}</td>
                                <td class="text-center">{{ ficha.estado }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Sin resultados -->
            <div v-if="termino && termino.trim() !== '' && totalResultados === 0" class="card-tema">
                <p class="tema-texto-secundario text-center py-8">
                    No se encontraron resultados para "{{ termino }}"
                </p>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { computed } from 'vue';
import AppLayout from '@/Layouts/AppLayout.vue';

const props = defineProps({
    resultados: {
        type: Object,
        default: () => ({
            usuarios: [],
            salas: [],
            servicios: [],
            fichas: [],
        }),
    },
    termino: {
        type: String,
        default: '',
    },
});

const totalResultados = computed(() => {
    return (props.resultados.usuarios?.length || 0) +
           (props.resultados.salas?.length || 0) +
           (props.resultados.servicios?.length || 0) +
           (props.resultados.fichas?.length || 0);
});
</script>

