<template>
    <AppLayout title="Gestión de Seguimientos">
        <template #header>
            <h2 class="tema-titulo">Gestión de Seguimientos</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Seguimientos</h3>
                    <button
                        v-if="tienePermiso('crear-seguimientos')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Seguimiento"
                    >
                        +
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Ficha</th>
                                <th>Tipo</th>
                                <th>Fecha</th>
                                <th>Nivel Urgencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="seguimiento in seguimientos.data" :key="seguimiento.id">
                                <td class="text-center">{{ seguimiento.id }}</td>
                                <td class="tema-texto">{{ seguimiento.ficha?.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ seguimiento.tipo }}</td>
                                <td class="text-center">{{ new Date(seguimiento.fecha).toLocaleDateString() }}</td>
                                <td class="text-center">
                                    <span :class="getUrgenciaClass(seguimiento.nivel_urgencia)">
                                        {{ seguimiento.nivel_urgencia || 'N/A' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-seguimientos')"
                                        @click="abrirModalMostrar(seguimiento.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar seguimiento"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-seguimientos')"
                                        @click="abrirModalEditar(seguimiento.id)"
                                        class="btn-tema mr-2"
                                        title="Editar seguimiento"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-seguimientos')"
                                        @click="eliminarSeguimiento(seguimiento.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar seguimiento"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="seguimientos.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in seguimientos.links"
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
        <template #title>Crear Seguimiento</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="seguimiento-ficha" value="Ficha *" />
                    <select id="seguimiento-ficha" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.ficha_id">
                        <option value="">Seleccione...</option>
                        <option v-for="ficha in fichasDisponibles" :key="ficha.id" :value="ficha.id">
                            {{ ficha.cliente?.usuario?.persona?.nombre_completo || ficha.id }} - {{ ficha.fecha }}
                        </option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.ficha_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-tipo" value="Tipo *" />
                    <select id="seguimiento-tipo" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.tipo">
                        <option value="">Seleccione...</option>
                        <option value="TRIAGE">Triage</option>
                        <option value="CONSULTA">Consulta</option>
                        <option value="TRATAMIENTO">Tratamiento</option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.tipo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-fecha" value="Fecha" />
                    <TextInput id="seguimiento-fecha" type="datetime-local" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.fecha" />
                    <InputError :message="formularioSeguimiento.errors.fecha" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-urgencia" value="Nivel de Urgencia" />
                    <select id="seguimiento-urgencia" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.nivel_urgencia">
                        <option value="">Seleccione...</option>
                        <option value="BAJA">Baja</option>
                        <option value="MEDIA">Media</option>
                        <option value="ALTA">Alta</option>
                        <option value="URGENTE">Urgente</option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.nivel_urgencia" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-motivo" value="Motivo de Consulta" />
                    <textarea id="seguimiento-motivo" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.motivo_consulta"></textarea>
                    <InputError :message="formularioSeguimiento.errors.motivo_consulta" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-diagnostico" value="Diagnóstico" />
                    <textarea id="seguimiento-diagnostico" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.diagnostico"></textarea>
                    <InputError :message="formularioSeguimiento.errors.diagnostico" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-observaciones" value="Observaciones" />
                    <textarea id="seguimiento-observaciones" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.observaciones"></textarea>
                    <InputError :message="formularioSeguimiento.errors.observaciones" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-tratamiento" value="Tratamiento Prescrito" />
                    <textarea id="seguimiento-tratamiento" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.tratamiento_prescrito"></textarea>
                    <InputError :message="formularioSeguimiento.errors.tratamiento_prescrito" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarSeguimiento" :disabled="formularioSeguimiento.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Seguimiento</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="seguimiento-ficha-edit" value="Ficha *" />
                    <select id="seguimiento-ficha-edit" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.ficha_id">
                        <option v-for="ficha in fichasDisponibles" :key="ficha.id" :value="ficha.id">
                            {{ ficha.cliente?.usuario?.persona?.nombre_completo || ficha.id }} - {{ ficha.fecha }}
                        </option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.ficha_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-tipo-edit" value="Tipo *" />
                    <select id="seguimiento-tipo-edit" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.tipo">
                        <option value="TRIAGE">Triage</option>
                        <option value="CONSULTA">Consulta</option>
                        <option value="TRATAMIENTO">Tratamiento</option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.tipo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-fecha-edit" value="Fecha *" />
                    <TextInput id="seguimiento-fecha-edit" type="datetime-local" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.fecha" />
                    <InputError :message="formularioSeguimiento.errors.fecha" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="seguimiento-urgencia-edit" value="Nivel de Urgencia" />
                    <select id="seguimiento-urgencia-edit" class="mt-1 block w-full input-tema" v-model="formularioSeguimiento.nivel_urgencia">
                        <option value="">Seleccione...</option>
                        <option value="BAJA">Baja</option>
                        <option value="MEDIA">Media</option>
                        <option value="ALTA">Alta</option>
                        <option value="URGENTE">Urgente</option>
                    </select>
                    <InputError :message="formularioSeguimiento.errors.nivel_urgencia" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-motivo-edit" value="Motivo de Consulta" />
                    <textarea id="seguimiento-motivo-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.motivo_consulta"></textarea>
                    <InputError :message="formularioSeguimiento.errors.motivo_consulta" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-diagnostico-edit" value="Diagnóstico" />
                    <textarea id="seguimiento-diagnostico-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.diagnostico"></textarea>
                    <InputError :message="formularioSeguimiento.errors.diagnostico" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-observaciones-edit" value="Observaciones" />
                    <textarea id="seguimiento-observaciones-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.observaciones"></textarea>
                    <InputError :message="formularioSeguimiento.errors.observaciones" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="seguimiento-tratamiento-edit" value="Tratamiento Prescrito" />
                    <textarea id="seguimiento-tratamiento-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSeguimiento.tratamiento_prescrito"></textarea>
                    <InputError :message="formularioSeguimiento.errors.tratamiento_prescrito" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarSeguimiento" :disabled="formularioSeguimiento.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle del Seguimiento</template>
        <template #content>
            <div v-if="seguimientoMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ seguimientoMostrado.id }}</p>
                <p><strong>Ficha:</strong> {{ seguimientoMostrado.ficha?.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</p>
                <p><strong>Tipo:</strong> {{ seguimientoMostrado.tipo }}</p>
                <p><strong>Fecha:</strong> {{ new Date(seguimientoMostrado.fecha).toLocaleString() }}</p>
                <p><strong>Nivel de Urgencia:</strong> {{ seguimientoMostrado.nivel_urgencia || 'N/A' }}</p>
                <p><strong>Motivo:</strong> {{ seguimientoMostrado.motivo_consulta || 'Sin motivo' }}</p>
                <p><strong>Diagnóstico:</strong> {{ seguimientoMostrado.diagnostico || 'Sin diagnóstico' }}</p>
                <p><strong>Observaciones:</strong> {{ seguimientoMostrado.observaciones || 'Sin observaciones' }}</p>
                <p><strong>Tratamiento:</strong> {{ seguimientoMostrado.tratamiento_prescrito || 'Sin tratamiento' }}</p>
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
    seguimientos: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const seguimientoMostrado = ref(null);
const fichasDisponibles = ref([]);

const formularioSeguimiento = useForm({
    id: null,
    ficha_id: '',
    tipo: '',
    fecha: '',
    signos_vitales: null,
    motivo_consulta: '',
    nivel_urgencia: '',
    diagnostico: '',
    observaciones: '',
    tratamiento_prescrito: '',
    medicamentos: null,
});

function getUrgenciaClass(urgencia) {
    const clases = {
        'BAJA': 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs',
        'MEDIA': 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs',
        'ALTA': 'bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs',
        'URGENTE': 'bg-red-100 text-red-800 px-2 py-1 rounded text-xs',
    };
    return clases[urgencia] || 'px-2 py-1 rounded text-xs';
}

function abrirModalCrear() {
    formularioSeguimiento.reset();
    formularioSeguimiento.clearErrors();
    axios.get(route('seguimientos.create'))
        .then(response => {
            fichasDisponibles.value = response.data.fichas || [];
            modalCrear.value = true;
        });
}

function abrirModalMostrar(id) {
    axios.get(route('seguimientos.show', id))
        .then(response => {
            seguimientoMostrado.value = response.data.seguimiento;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('seguimientos.edit', id))
        .then(response => {
            const { seguimiento, fichas } = response.data;
            formularioSeguimiento.id = seguimiento.id;
            formularioSeguimiento.ficha_id = seguimiento.ficha_id;
            formularioSeguimiento.tipo = seguimiento.tipo;
            formularioSeguimiento.fecha = seguimiento.fecha ? new Date(seguimiento.fecha).toISOString().slice(0, 16) : '';
            formularioSeguimiento.signos_vitales = seguimiento.signos_vitales;
            formularioSeguimiento.motivo_consulta = seguimiento.motivo_consulta || '';
            formularioSeguimiento.nivel_urgencia = seguimiento.nivel_urgencia || '';
            formularioSeguimiento.diagnostico = seguimiento.diagnostico || '';
            formularioSeguimiento.observaciones = seguimiento.observaciones || '';
            formularioSeguimiento.tratamiento_prescrito = seguimiento.tratamiento_prescrito || '';
            formularioSeguimiento.medicamentos = seguimiento.medicamentos;
            fichasDisponibles.value = fichas || [];
            modalEditar.value = true;
        });
}

function guardarSeguimiento() {
    formularioSeguimiento.post(route('seguimientos.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarSeguimiento() {
    formularioSeguimiento.put(route('seguimientos.update', formularioSeguimiento.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarSeguimiento(id) {
    if (confirm('¿Está seguro de eliminar este seguimiento?')) {
        router.delete(route('seguimientos.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioSeguimiento.reset();
    formularioSeguimiento.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioSeguimiento.reset();
    formularioSeguimiento.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    seguimientoMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

