<template>
    <AppLayout title="Gestión de Reportes">
        <template #header>
            <h2 class="tema-titulo">Gestión de Reportes</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Reportes</h3>
                    <button
                        v-if="tienePermiso('crear-reportes')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Reporte"
                    >
                        +
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Generado por</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="reporte in reportes.data" :key="reporte.id">
                                <td class="text-center">{{ reporte.id }}</td>
                                <td class="tema-texto font-bold">{{ reporte.nombre }}</td>
                                <td class="text-center">{{ reporte.tipo }}</td>
                                <td class="text-center">
                                    <span :class="getEstadoClass(reporte.estado)">
                                        {{ reporte.estado }}
                                    </span>
                                </td>
                                <td class="text-center">{{ reporte.usuario_generador?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ new Date(reporte.created_at).toLocaleDateString() }}</td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-reportes')"
                                        @click="abrirModalMostrar(reporte.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar reporte"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-reportes')"
                                        @click="abrirModalEditar(reporte.id)"
                                        class="btn-tema mr-2"
                                        title="Editar reporte"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-reportes')"
                                        @click="eliminarReporte(reporte.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar reporte"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="reportes.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in reportes.links"
                            :key="link.label"
                            :href="link.url || '#'"
                            v-html="link.label"
                            :class="[
                                'px-3 py-2 mx-1 rounded',
                                link.active ? 'btn-tema' : 'btn-tema-secundario',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <footer class="mt-8 py-4 tema-fondo-secundario text-center">
            <p class="tema-texto-secundario">
                Visitas a esta página: <span class="font-bold">{{ contadorVisitas }}</span>
            </p>
        </footer>
    </AppLayout>

    <DialogModal :show="modalCrear" @close="cerrarModalCrear">
        <template #title>Crear Reporte</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="reporte-tipo" value="Tipo *" />
                    <select id="reporte-tipo" class="mt-1 block w-full input-tema" v-model="formularioReporte.tipo">
                        <option value="">Seleccione...</option>
                        <option value="FINANCIERO">Financiero</option>
                        <option value="CLINICO">Clínico</option>
                        <option value="OPERATIVO">Operativo</option>
                    </select>
                    <InputError :message="formularioReporte.errors.tipo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-nombre" value="Nombre *" />
                    <TextInput id="reporte-nombre" type="text" class="mt-1 block w-full input-tema" v-model="formularioReporte.nombre" />
                    <InputError :message="formularioReporte.errors.nombre" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-estado" value="Estado" />
                    <select id="reporte-estado" class="mt-1 block w-full input-tema" v-model="formularioReporte.estado">
                        <option value="GENERANDO">Generando</option>
                        <option value="COMPLETADO">Completado</option>
                        <option value="ERROR">Error</option>
                    </select>
                    <InputError :message="formularioReporte.errors.estado" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-url" value="URL del Archivo" />
                    <TextInput id="reporte-url" type="text" class="mt-1 block w-full input-tema" v-model="formularioReporte.url_archivo" />
                    <InputError :message="formularioReporte.errors.url_archivo" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarReporte" :disabled="formularioReporte.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Reporte</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="reporte-tipo-edit" value="Tipo *" />
                    <select id="reporte-tipo-edit" class="mt-1 block w-full input-tema" v-model="formularioReporte.tipo">
                        <option value="FINANCIERO">Financiero</option>
                        <option value="CLINICO">Clínico</option>
                        <option value="OPERATIVO">Operativo</option>
                    </select>
                    <InputError :message="formularioReporte.errors.tipo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-nombre-edit" value="Nombre *" />
                    <TextInput id="reporte-nombre-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioReporte.nombre" />
                    <InputError :message="formularioReporte.errors.nombre" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-estado-edit" value="Estado *" />
                    <select id="reporte-estado-edit" class="mt-1 block w-full input-tema" v-model="formularioReporte.estado">
                        <option value="GENERANDO">Generando</option>
                        <option value="COMPLETADO">Completado</option>
                        <option value="ERROR">Error</option>
                    </select>
                    <InputError :message="formularioReporte.errors.estado" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="reporte-url-edit" value="URL del Archivo" />
                    <TextInput id="reporte-url-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioReporte.url_archivo" />
                    <InputError :message="formularioReporte.errors.url_archivo" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarReporte" :disabled="formularioReporte.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle del Reporte</template>
        <template #content>
            <div v-if="reporteMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ reporteMostrado.id }}</p>
                <p><strong>Nombre:</strong> {{ reporteMostrado.nombre }}</p>
                <p><strong>Tipo:</strong> {{ reporteMostrado.tipo }}</p>
                <p><strong>Estado:</strong> {{ reporteMostrado.estado }}</p>
                <p><strong>Generado por:</strong> {{ reporteMostrado.usuario_generador?.persona?.nombre_completo || 'N/A' }}</p>
                <p><strong>URL del Archivo:</strong> {{ reporteMostrado.url_archivo || 'Sin archivo' }}</p>
                <p><strong>Fecha:</strong> {{ new Date(reporteMostrado.created_at).toLocaleString() }}</p>
            </div>
        </template>
        <template #footer>
            <PrimaryButton @click="cerrarModalMostrar">Cerrar</PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { tienePermiso } from '@/Permisos_Ayuda/permisos.js';

const props = defineProps({
    reportes: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const reporteMostrado = ref(null);

const formularioReporte = useForm({
    id: null,
    tipo: '',
    nombre: '',
    parametros: null,
    url_archivo: '',
    estado: 'GENERANDO',
});

function getEstadoClass(estado) {
    const clases = {
        'GENERANDO': 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs',
        'COMPLETADO': 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs',
        'ERROR': 'bg-red-100 text-red-800 px-2 py-1 rounded text-xs',
    };
    return clases[estado] || 'px-2 py-1 rounded text-xs';
}

function abrirModalCrear() {
    formularioReporte.reset();
    formularioReporte.clearErrors();
    formularioReporte.estado = 'GENERANDO';
    modalCrear.value = true;
}

function abrirModalMostrar(id) {
    axios.get(route('reportes.show', id))
        .then(response => {
            reporteMostrado.value = response.data.reporte;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('reportes.edit', id))
        .then(response => {
            const { reporte } = response.data;
            formularioReporte.id = reporte.id;
            formularioReporte.tipo = reporte.tipo;
            formularioReporte.nombre = reporte.nombre;
            formularioReporte.parametros = reporte.parametros;
            formularioReporte.url_archivo = reporte.url_archivo || '';
            formularioReporte.estado = reporte.estado;
            modalEditar.value = true;
        });
}

function guardarReporte() {
    formularioReporte.post(route('reportes.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarReporte() {
    formularioReporte.put(route('reportes.update', formularioReporte.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarReporte(id) {
    if (confirm('¿Está seguro de eliminar este reporte?')) {
        router.delete(route('reportes.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioReporte.reset();
    formularioReporte.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioReporte.reset();
    formularioReporte.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    reporteMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

