<template>
    <AppLayout title="Gestión de Permisos">
        <template #header>
            <h2 class="tema-titulo">Gestión de Permisos</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <!-- Botón Crear -->
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Permisos</h3>
                    <button
                        v-if="tienePermiso('crear-permisos')"
                        @click="abrirModalCrear"
                        class="btn-tema"
                        title="Crear permiso"
                    >
                        +
                    </button>
                </div>

                <!-- Tabla de Permisos -->
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="permiso in permisos.data" :key="permiso.id" >
                                <td class="text-center">{{ permiso.id }}</td>
                                <td class="tema-texto">{{ permiso.name }}</td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-permisos')"
                                        @click="abrirModalMostrar(permiso.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar permiso"
                                        aria-label="Mostrar permiso"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-permisos')"
                                        @click="abrirModalEditar(permiso.id)"
                                        class="btn-tema mr-2"
                                        title="Editar permiso"
                                        aria-label="Editar permiso"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-permisos')"
                                        @click="eliminarPermiso(permiso.id)"
                                        class="btn-tema-secundario"
                                        tittle="Eliminar permiso"
                                        aria-label="Eliminar permiso"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4" v-if="permisos.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in permisos.links"
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

        <!-- Footer con contador de visitas -->
        <footer class="mt-8 py-4 tema-fondo-secundario text-center">
            <p class="tema-texto-secundario">
                Visitas a esta página: <span class="font-bold">{{ contadorVisitas }}</span>
            </p>
        </footer>
    </AppLayout>

    <!-- Modal Crear -->
    <DialogModal :show="modalCrear" @close="cerrarModalCrear">
        <template #title>
            Crear Permiso
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="permiso-name" value="Nombre del Permiso" />
                    <TextInput
                        id="permiso-name"
                        type="text"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioPermiso.name"
                    />
                    <InputError :message="formularioPermiso.errors.name" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="guardarPermiso" :disabled="formularioPermiso.processing">
                Guardar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Editar -->
    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>
            Editar Permiso
        </template>

        <template #content>
            <div>
                <InputLabel for="permiso-name-edit" value="Nombre del Permiso" />
                <TextInput
                    id="permiso-name-edit"
                    type="text"
                    class="mt-1 block w-full input-tema"
                    v-model="formularioPermiso.name"
                />
                <InputError :message="formularioPermiso.errors.name" class="mt-2" />
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="actualizarPermiso" :disabled="formularioPermiso.processing">
                Actualizar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Mostrar -->
    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>
            Detalle del Permiso
        </template>

        <template #content>
            <div v-if="permisoMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ permisoMostrado.id }}</p>
                <p><strong>Nombre:</strong> {{ permisoMostrado.name }}</p>
            </div>
        </template>

        <template #footer>
            <PrimaryButton @click="cerrarModalMostrar">
                Cerrar
            </PrimaryButton>
        </template>
    </DialogModal>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    permisos: Object,
    contadorVisitas: Number,
});

const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const permisoMostrado = ref(null);

const formularioPermiso = useForm({
    id: null,
    name: '',
});

function abrirModalCrear() {
    formularioPermiso.reset();
    formularioPermiso.clearErrors();
    modalCrear.value = true;
}

function abrirModalMostrar(id) {
    axios.get(route('permisos.show', id))
        .then(response => {
            permisoMostrado.value = response.data.permiso;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('permisos.edit', id))
        .then(response => {
            formularioPermiso.id = response.data.permiso.id;
            formularioPermiso.name = response.data.permiso.name;
            modalEditar.value = true;
        });
}

function guardarPermiso() {
    formularioPermiso.post(route('permisos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalCrear();
        },
    });
}

function actualizarPermiso() {
    formularioPermiso.put(route('permisos.update', formularioPermiso.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalEditar();
        },
    });
}

function eliminarPermiso(id) {
    if (confirm('¿Está seguro de eliminar este permiso?')) {
        router.delete(route('permisos.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioPermiso.reset();
    formularioPermiso.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioPermiso.reset();
    formularioPermiso.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    permisoMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

<style scoped>
/* Los estilos usan las variables CSS del tema */
</style>

