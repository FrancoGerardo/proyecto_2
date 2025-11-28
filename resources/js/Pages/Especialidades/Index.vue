<template>
    <AppLayout title="Gestión de Especialidades">
        <template #header>
            <h2 class="tema-titulo">Gestión de Especialidades</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Especialidades</h3>
                    <button
                        v-if="tienePermiso('crear-especialidades')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Especialidad"
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
                                <th>Descripción</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="especialidad in especialidades.data" :key="especialidad.id">
                                <td class="text-center">{{ especialidad.id }}</td>
                                <td class="tema-texto font-bold">{{ especialidad.nombre }}</td>
                                <td class="tema-texto">{{ especialidad.descripcion || 'Sin descripción' }}</td>
                                <td class="text-center">
                                    <span :class="especialidad.estado === 'ACTIVA' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' + ' px-2 py-1 rounded text-xs'">
                                        {{ especialidad.estado }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-especialidades')"
                                        @click="abrirModalMostrar(especialidad.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar especialidad"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-especialidades')"
                                        @click="abrirModalEditar(especialidad.id)"
                                        class="btn-tema mr-2"
                                        title="Editar especialidad"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-especialidades')"
                                        @click="eliminarEspecialidad(especialidad.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar especialidad"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="especialidades.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in especialidades.links"
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
        <template #title>Crear Especialidad</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <InputLabel for="especialidad-nombre" value="Nombre *" />
                    <TextInput id="especialidad-nombre" type="text" class="mt-1 block w-full input-tema" v-model="formularioEspecialidad.nombre" />
                    <InputError :message="formularioEspecialidad.errors.nombre" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="especialidad-descripcion" value="Descripción" />
                    <textarea id="especialidad-descripcion" class="mt-1 block w-full input-tema" rows="3" v-model="formularioEspecialidad.descripcion"></textarea>
                    <InputError :message="formularioEspecialidad.errors.descripcion" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="especialidad-estado" value="Estado" />
                    <select id="especialidad-estado" class="mt-1 block w-full input-tema" v-model="formularioEspecialidad.estado">
                        <option value="ACTIVA">Activa</option>
                        <option value="INACTIVA">Inactiva</option>
                    </select>
                    <InputError :message="formularioEspecialidad.errors.estado" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarEspecialidad" :disabled="formularioEspecialidad.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Especialidad</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <InputLabel for="especialidad-nombre-edit" value="Nombre *" />
                    <TextInput id="especialidad-nombre-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioEspecialidad.nombre" />
                    <InputError :message="formularioEspecialidad.errors.nombre" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="especialidad-descripcion-edit" value="Descripción" />
                    <textarea id="especialidad-descripcion-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioEspecialidad.descripcion"></textarea>
                    <InputError :message="formularioEspecialidad.errors.descripcion" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="especialidad-estado-edit" value="Estado *" />
                    <select id="especialidad-estado-edit" class="mt-1 block w-full input-tema" v-model="formularioEspecialidad.estado">
                        <option value="ACTIVA">Activa</option>
                        <option value="INACTIVA">Inactiva</option>
                    </select>
                    <InputError :message="formularioEspecialidad.errors.estado" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarEspecialidad" :disabled="formularioEspecialidad.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle de la Especialidad</template>
        <template #content>
            <div v-if="especialidadMostrada" class="space-y-3">
                <p><strong>ID:</strong> {{ especialidadMostrada.id }}</p>
                <p><strong>Nombre:</strong> {{ especialidadMostrada.nombre }}</p>
                <p><strong>Descripción:</strong> {{ especialidadMostrada.descripcion || 'Sin descripción' }}</p>
                <p><strong>Estado:</strong> {{ especialidadMostrada.estado }}</p>
                <div v-if="especialidadMostrada.medicos && especialidadMostrada.medicos.length > 0">
                    <p><strong>Médicos asociados:</strong></p>
                    <ul class="list-disc list-inside">
                        <li v-for="medico in especialidadMostrada.medicos" :key="medico.usuario_id">
                            {{ medico.usuario?.persona?.nombre }} {{ medico.usuario?.persona?.apellidos }}
                        </li>
                    </ul>
                </div>
                <div v-if="especialidadMostrada.servicios && especialidadMostrada.servicios.length > 0">
                    <p><strong>Servicios asociados:</strong></p>
                    <ul class="list-disc list-inside">
                        <li v-for="servicio in especialidadMostrada.servicios" :key="servicio.id">
                            {{ servicio.nombre }}
                        </li>
                    </ul>
                </div>
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
    especialidades: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const especialidadMostrada = ref(null);

const formularioEspecialidad = useForm({
    id: null,
    nombre: '',
    descripcion: '',
    estado: 'ACTIVA',
});

function abrirModalCrear() {
    formularioEspecialidad.reset();
    formularioEspecialidad.clearErrors();
    formularioEspecialidad.estado = 'ACTIVA';
    modalCrear.value = true;
}

function abrirModalMostrar(id) {
    axios.get(route('especialidades.show', id))
        .then(response => {
            especialidadMostrada.value = response.data.especialidad;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('especialidades.edit', id))
        .then(response => {
            const { especialidad } = response.data;
            formularioEspecialidad.id = especialidad.id;
            formularioEspecialidad.nombre = especialidad.nombre;
            formularioEspecialidad.descripcion = especialidad.descripcion || '';
            formularioEspecialidad.estado = especialidad.estado;
            modalEditar.value = true;
        });
}

function guardarEspecialidad() {
    formularioEspecialidad.post(route('especialidades.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarEspecialidad() {
    formularioEspecialidad.put(route('especialidades.update', formularioEspecialidad.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarEspecialidad(id) {
    if (confirm('¿Está seguro de eliminar esta especialidad?')) {
        router.delete(route('especialidades.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioEspecialidad.reset();
    formularioEspecialidad.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioEspecialidad.reset();
    formularioEspecialidad.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    especialidadMostrada.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

