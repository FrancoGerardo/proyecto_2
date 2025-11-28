<template>
    <AppLayout title="Gestión de Servicios">
        <template #header>
            <h2 class="tema-titulo">Gestión de Servicios</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Servicios</h3>
                    <button
                        v-if="tienePermiso('crear-servicios')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Servicio"
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
                                <th>Categoría</th>
                                <th>Costo</th>
                                <th>Duración (min)</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="servicio in servicios.data" :key="servicio.id">
                                <td class="text-center">{{ servicio.id }}</td>
                                <td class="tema-texto font-bold">{{ servicio.nombre }}</td>
                                <td class="text-center">{{ servicio.categoria }}</td>
                                <td class="text-center">Bs. {{ servicio.costo }}</td>
                                <td class="text-center">{{ servicio.duracion_minutos || 'N/A' }}</td>
                                <td class="text-center">
                                    <span :class="servicio.estado ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' + ' px-2 py-1 rounded text-xs'">
                                        {{ servicio.estado ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-servicios')"
                                        @click="abrirModalMostrar(servicio.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar servicio"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-servicios')"
                                        @click="abrirModalEditar(servicio.id)"
                                        class="btn-tema mr-2"
                                        title="Editar servicio"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-servicios')"
                                        @click="eliminarServicio(servicio.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar servicio"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="servicios.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in servicios.links"
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
        <template #title>Crear Servicio</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <InputLabel for="servicio-nombre" value="Nombre *" />
                    <TextInput id="servicio-nombre" type="text" class="mt-1 block w-full input-tema" v-model="formularioServicio.nombre" />
                    <InputError :message="formularioServicio.errors.nombre" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-categoria" value="Categoría *" />
                    <select id="servicio-categoria" class="mt-1 block w-full input-tema" v-model="formularioServicio.categoria">
                        <option value="">Seleccione...</option>
                        <option value="INTERNACION">Internación</option>
                        <option value="ESPECIALIDAD">Especialidad</option>
                        <option value="ENFERMERIA">Enfermería</option>
                    </select>
                    <InputError :message="formularioServicio.errors.categoria" class="mt-2" />
                </div>
                <div v-if="formularioServicio.categoria === 'ESPECIALIDAD'" class="md:col-span-2">
                    <InputLabel for="servicio-especialidad" value="Especialidad (solo con médicos) *" />
                    <select
                        id="servicio-especialidad"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioServicio.especialidad_id"
                    >
                        <option value="">Seleccione especialidad...</option>
                        <option
                            v-for="especialidad in especialidadesConMedicos"
                            :key="especialidad.id"
                            :value="especialidad.id"
                        >
                            {{ especialidad.nombre }}
                        </option>
                    </select>
                    <p v-if="especialidadesConMedicos.length === 0" class="text-xs tema-texto-secundario mt-1">
                        No existen especialidades con médicos registrados.
                    </p>
                    <InputError :message="formularioServicio.errors.especialidad_id" class="mt-2" />
                </div>
                <div v-if="formularioServicio.categoria === 'ESPECIALIDAD'" class="md:col-span-2">
                    <InputLabel value="Médico responsable *" />
                    <div v-if="medicosFiltrados.length > 0" class="border border-dashed tema-borde rounded p-3 mt-1 max-h-40 overflow-y-auto">
                        <label
                            v-for="medico in medicosFiltrados"
                            :key="medico.usuario_id"
                            class="flex items-center py-1 cursor-pointer hover:bg-gray-50 rounded px-2 tema-texto"
                        >
                            <input
                                type="radio"
                                class="mr-2"
                                name="medico-especialidad"
                                :value="medico.usuario_id"
                                v-model="formularioServicio.medico_id"
                            />
                            <span>{{ obtenerNombreMedico(medico) }}</span>
                        </label>
                    </div>
                    <p v-else class="text-xs tema-texto-secundario mt-1">
                        No hay médicos registrados para esta especialidad.
                    </p>
                    <InputError :message="formularioServicio.errors.medico_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-costo" value="Costo *" />
                    <TextInput id="servicio-costo" type="number" step="0.01" class="mt-1 block w-full input-tema" v-model="formularioServicio.costo" />
                    <InputError :message="formularioServicio.errors.costo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-duracion" value="Duración (minutos)" />
                    <TextInput id="servicio-duracion" type="number" class="mt-1 block w-full input-tema" v-model="formularioServicio.duracion_minutos" />
                    <InputError :message="formularioServicio.errors.duracion_minutos" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="servicio-descripcion" value="Descripción" />
                    <textarea id="servicio-descripcion" class="mt-1 block w-full input-tema" rows="3" v-model="formularioServicio.descripcion"></textarea>
                    <InputError :message="formularioServicio.errors.descripcion" class="mt-2" />
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" v-model="formularioServicio.estado" class="mr-2" />
                        <span class="tema-texto">Activo</span>
                    </label>
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarServicio" :disabled="formularioServicio.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Servicio</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <InputLabel for="servicio-nombre-edit" value="Nombre *" />
                    <TextInput id="servicio-nombre-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioServicio.nombre" />
                    <InputError :message="formularioServicio.errors.nombre" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-categoria-edit" value="Categoría *" />
                    <select id="servicio-categoria-edit" class="mt-1 block w-full input-tema" v-model="formularioServicio.categoria">
                        <option value="INTERNACION">Internación</option>
                        <option value="ESPECIALIDAD">Especialidad</option>
                        <option value="ENFERMERIA">Enfermería</option>
                    </select>
                    <InputError :message="formularioServicio.errors.categoria" class="mt-2" />
                </div>
                <div v-if="formularioServicio.categoria === 'ESPECIALIDAD'" class="md:col-span-2">
                    <InputLabel for="servicio-especialidad-edit" value="Especialidad (solo con médicos) *" />
                    <select
                        id="servicio-especialidad-edit"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioServicio.especialidad_id"
                    >
                        <option value="">Seleccione especialidad...</option>
                        <option
                            v-for="especialidad in especialidadesConMedicos"
                            :key="especialidad.id"
                            :value="especialidad.id"
                        >
                            {{ especialidad.nombre }}
                        </option>
                    </select>
                    <p v-if="especialidadesConMedicos.length === 0" class="text-xs tema-texto-secundario mt-1">
                        No existen especialidades con médicos registrados.
                    </p>
                    <InputError :message="formularioServicio.errors.especialidad_id" class="mt-2" />
                </div>
                <div v-if="formularioServicio.categoria === 'ESPECIALIDAD'" class="md:col-span-2">
                    <InputLabel value="Médico responsable *" />
                    <div v-if="medicosFiltrados.length > 0" class="border border-dashed tema-borde rounded p-3 mt-1 max-h-40 overflow-y-auto">
                        <label
                            v-for="medico in medicosFiltrados"
                            :key="medico.usuario_id"
                            class="flex items-center py-1 cursor-pointer hover:bg-gray-50 rounded px-2 tema-texto"
                        >
                            <input
                                type="radio"
                                class="mr-2"
                                name="medico-especialidad-edit"
                                :value="medico.usuario_id"
                                v-model="formularioServicio.medico_id"
                            />
                            <span>{{ obtenerNombreMedico(medico) }}</span>
                        </label>
                    </div>
                    <p v-else class="text-xs tema-texto-secundario mt-1">
                        No hay médicos registrados para esta especialidad.
                    </p>
                    <InputError :message="formularioServicio.errors.medico_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-costo-edit" value="Costo *" />
                    <TextInput id="servicio-costo-edit" type="number" step="0.01" class="mt-1 block w-full input-tema" v-model="formularioServicio.costo" />
                    <InputError :message="formularioServicio.errors.costo" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="servicio-duracion-edit" value="Duración (minutos)" />
                    <TextInput id="servicio-duracion-edit" type="number" class="mt-1 block w-full input-tema" v-model="formularioServicio.duracion_minutos" />
                    <InputError :message="formularioServicio.errors.duracion_minutos" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="servicio-descripcion-edit" value="Descripción" />
                    <textarea id="servicio-descripcion-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioServicio.descripcion"></textarea>
                    <InputError :message="formularioServicio.errors.descripcion" class="mt-2" />
                </div>
                <div>
                    <label class="flex items-center">
                        <input type="checkbox" v-model="formularioServicio.estado" class="mr-2" />
                        <span class="tema-texto">Activo</span>
                    </label>
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarServicio" :disabled="formularioServicio.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle del Servicio</template>
        <template #content>
            <div v-if="servicioMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ servicioMostrado.id }}</p>
                <p><strong>Nombre:</strong> {{ servicioMostrado.nombre }}</p>
                <p><strong>Categoría:</strong> {{ servicioMostrado.categoria }}</p>
                <p><strong>Costo:</strong> Bs. {{ servicioMostrado.costo }}</p>
                <p><strong>Duración:</strong> {{ servicioMostrado.duracion_minutos || 'N/A' }} minutos</p>
                <p><strong>Estado:</strong> {{ servicioMostrado.estado ? 'Activo' : 'Inactivo' }}</p>
                <p><strong>Descripción:</strong> {{ servicioMostrado.descripcion || 'Sin descripción' }}</p>
            </div>
        </template>
        <template #footer>
            <PrimaryButton @click="cerrarModalMostrar">Cerrar</PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
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
    servicios: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const servicioMostrado = ref(null);
const especialidadesConMedicos = ref([]);
const medicosDisponibles = ref([]);
const medicosFiltrados = ref([]);
const cargandoEspecialidades = ref(false);

const formularioServicio = useForm({
    id: null,
    nombre: '',
    descripcion: '',
    categoria: '',
    especialidad_id: '',
    medico_id: '',
    costo: '',
    duracion_minutos: '',
    estado: true,
});

function obtenerEspecialidadesConMedicos() {
    if (cargandoEspecialidades.value) {
        return;
    }

    // Si ya tenemos datos cargados no volvemos a llamar a la API
    if (especialidadesConMedicos.value.length > 0) {
        return;
    }

    cargandoEspecialidades.value = true;
    axios.get(route('servicios.create'))
        .then((response) => {
            especialidadesConMedicos.value = response.data.especialidades || [];
            medicosDisponibles.value = response.data.medicos || [];
            filtrarMedicosPorEspecialidad();
        })
        .catch((error) => {
            console.error('Error al cargar especialidades:', error);
        })
        .finally(() => {
            cargandoEspecialidades.value = false;
        });
}

function sincronizarNombreEspecialidad() {
    if (!formularioServicio.especialidad_id) {
        return;
    }
    const especialidad = especialidadesConMedicos.value.find(
        (esp) => esp.id === formularioServicio.especialidad_id,
    );
    if (especialidad) {
        formularioServicio.nombre = especialidad.nombre;
    }
}

function filtrarMedicosPorEspecialidad() {
    if (!formularioServicio.especialidad_id) {
        medicosFiltrados.value = [];
        formularioServicio.medico_id = '';
        return;
    }

    medicosFiltrados.value = medicosDisponibles.value.filter((medico) => {
        if (!Array.isArray(medico.especialidades)) {
            return false;
        }
        return medico.especialidades.some(
            (esp) => esp.id === formularioServicio.especialidad_id,
        );
    });

    if (
        formularioServicio.medico_id &&
        !medicosFiltrados.value.some((medico) => medico.usuario_id === formularioServicio.medico_id)
    ) {
        formularioServicio.medico_id = '';
    }
}

function obtenerNombreMedico(medico) {
    if (!medico) {
        return 'Sin datos';
    }
    const persona = medico.usuario?.persona;
    if (persona) {
        return `${persona.nombre || ''} ${persona.apellidos || ''}`.trim() || 'Médico sin nombre';
    }
    return medico.usuario?.name || 'Médico sin nombre';
}

function abrirModalCrear() {
    formularioServicio.reset();
    formularioServicio.clearErrors();
    formularioServicio.estado = true;
    formularioServicio.categoria = '';
    formularioServicio.especialidad_id = '';
    formularioServicio.medico_id = '';
    obtenerEspecialidadesConMedicos();
    modalCrear.value = true;
}

function abrirModalMostrar(id) {
    axios.get(route('servicios.show', id))
        .then(response => {
            servicioMostrado.value = response.data.servicio;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('servicios.edit', id))
        .then(response => {
            const { servicio } = response.data;
            especialidadesConMedicos.value = response.data.especialidades || [];
            medicosDisponibles.value = response.data.medicos || [];
            formularioServicio.id = servicio.id;
            formularioServicio.nombre = servicio.nombre;
            formularioServicio.descripcion = servicio.descripcion || '';
            formularioServicio.categoria = servicio.categoria;
            formularioServicio.especialidad_id = servicio.especialidad_id || '';
            formularioServicio.medico_id = servicio.meedico_id || servicio.medico_id || '';
            formularioServicio.costo = servicio.costo;
            formularioServicio.duracion_minutos = servicio.duracion_minutos || '';
            formularioServicio.estado = servicio.estado;
            if (formularioServicio.categoria === 'ESPECIALIDAD' && formularioServicio.especialidad_id) {
                sincronizarNombreEspecialidad();
                filtrarMedicosPorEspecialidad();
            }
            modalEditar.value = true;
        });
}

function guardarServicio() {
    formularioServicio.post(route('servicios.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarServicio() {
    formularioServicio.put(route('servicios.update', formularioServicio.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarServicio(id) {
    if (confirm('¿Está seguro de eliminar este servicio?')) {
        router.delete(route('servicios.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioServicio.reset();
    formularioServicio.clearErrors();
    formularioServicio.especialidad_id = '';
    formularioServicio.medico_id = '';
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioServicio.reset();
    formularioServicio.clearErrors();
    formularioServicio.especialidad_id = '';
    formularioServicio.medico_id = '';
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    servicioMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});

watch(() => formularioServicio.categoria, (nuevaCategoria) => {
    if (nuevaCategoria === 'ESPECIALIDAD') {
        obtenerEspecialidadesConMedicos();
    } else {
        formularioServicio.especialidad_id = '';
        formularioServicio.medico_id = '';
        formularioServicio.nombre = '';
        especialidadesConMedicos.value = [];
        medicosDisponibles.value = [];
        medicosFiltrados.value = [];
    }
});

watch(() => formularioServicio.especialidad_id, () => {
    sincronizarNombreEspecialidad();
    filtrarMedicosPorEspecialidad();
});
</script>

