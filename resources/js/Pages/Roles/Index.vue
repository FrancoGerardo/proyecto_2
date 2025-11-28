<template>
    <AppLayout title="Gestión de Roles">
        <template #header>
            <h2 class="tema-titulo">Gestión de Roles</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <!-- Botón Crear -->
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Roles</h3>
                    <button
                        v-if="tienePermiso('crear-roles')"
                        @click="abrirModalCrear"
                         class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear rol"
                    >
                        +
                    </button>
                </div>

                <!-- Tabla de Roles -->
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Permisos</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="rol in roles.data" :key="rol.id">
                                <td>{{ rol.id }}</td>
                                <td class="tema-texto">{{ rol.name }}</td>
                                <td>
                                    <span
                                        v-for="permiso in rol.permissions"
                                        :key="permiso.id"
                                        class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1 mb-1"
                                    >
                                        {{ permiso.name }}
                                    </span>
                                </td>
                                <td>
                                    <button
                                        v-if="tienePermiso('mostrar-roles')"
                                        @click="abrirModalMostrar(rol.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar rol"
                                        aria-label="Mostrar rol"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-roles')"
                                        @click="abrirModalEditar(rol.id)"
                                        class="btn-tema mr-2"
                                        title="Editar rol"
                                        aria-label="Editar rol"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-roles')"
                                        @click="eliminarRol(rol.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar rol"
                                        aria-label="Eliminar rol"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4" v-if="roles.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in roles.links"
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
            Crear Rol
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="rol-name" value="Nombre del Rol" />
                    <input
                        id="rol-name"
                        type="text"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioRol.name"
                        :disabled="formularioRol.processing"
                    />
                    <InputError :message="formularioRol.errors.name" class="mt-2" />
                </div>

                <div>
                    <InputLabel value="Permisos" />
                    <div class="max-h-48 overflow-y-auto border border-dashed tema-borde rounded p-3 mt-2">
                        <label
                            v-for="permiso in permisosDisponibles"
                            :key="permiso.id"
                            class="flex items-center mb-2 tema-texto"
                        >
                            <input
                                type="checkbox"
                                class="mr-2"
                                :value="permiso.id"
                                v-model="formularioRol.permisos"
                            />
                            {{ permiso.name }}
                        </label>
                    </div>
                    <InputError :message="formularioRol.errors.permisos" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="guardarRol" :disabled="formularioRol.processing">
                Guardar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Editar -->
    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>
            Editar Rol
        </template>

        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="rol-name-edit" value="Nombre del Rol" />
                    <TextInput
                        id="rol-name-edit"
                        type="text"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioRol.name"
                    />
                    <InputError :message="formularioRol.errors.name" class="mt-2" />
                </div>

                <div>
                    <InputLabel value="Permisos" />
                    <div class="max-h-48 overflow-y-auto border border-dashed tema-borde rounded p-3 mt-2">
                        <label
                            v-for="permiso in permisosDisponibles"
                            :key="permiso.id"
                            class="flex items-center mb-2 tema-texto"
                        >
                            <input
                                type="checkbox"
                                class="mr-2"
                                :value="permiso.id"
                                v-model="formularioRol.permisos"
                            />
                            {{ permiso.name }}
                        </label>
                    </div>
                    <InputError :message="formularioRol.errors.permisos" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="actualizarRol" :disabled="formularioRol.processing">
                Actualizar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Mostrar -->
    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>
            Detalle del Rol
        </template>

        <template #content>
            <div v-if="rolMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ rolMostrado.id }}</p>
                <p><strong>Nombre:</strong> {{ rolMostrado.name }}</p>
                <div>
                    <strong>Permisos:</strong>
                    <div class="mt-2">
                        <span
                            v-for="permiso in rolMostrado.permissions"
                            :key="permiso.id"
                            class="inline-block bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs mr-1 mb-1"
                        >
                            {{ permiso.name }}
                        </span>
                        <span v-if="rolMostrado.permissions.length === 0" class="tema-texto-secundario text-sm">
                            Sin permisos asignados
                        </span>
                    </div>
                </div>
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
import { ref, onMounted, watch } from 'vue';
import { Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    roles: Object,
    permisos: Array,
    contadorVisitas: Number,
});

const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const rolMostrado = ref(null);

const formularioRol = useForm({
    id: null,
    name: '',
    permisos: [],
});

const permisosDisponibles = ref(props.permisos || []);

watch(() => props.permisos, (nuevos) => {
    permisosDisponibles.value = nuevos || [];
});

function abrirModalCrear() {
    // Resetear formulario
    formularioRol.reset();
    formularioRol.clearErrors();
    formularioRol.name = '';
    formularioRol.permisos = [];

    // Abrir modal primero
    modalCrear.value = true;

    // Cargar permisos desde el backend
    axios.get(route('roles.create'))
        .then(response => {
            if (response.data.permisos) {
                permisosDisponibles.value = response.data.permisos;
            }
        })
        .catch(error => {
            console.error('Error al cargar permisos:', error);
        });
}

function guardarRol() {
    formularioRol.post(route('roles.store'), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalCrear();
        },
    });
}

function abrirModalMostrar(id) {
    axios.get(route('roles.show', id))
        .then(response => {
            rolMostrado.value = response.data.rol;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('roles.edit', id))
        .then(response => {
            const { rol, permisos } = response.data;
            formularioRol.id = rol.id;
            formularioRol.name = rol.name;
            formularioRol.permisos = rol.permissions?.map((permiso) => permiso.id) || [];
            // Actualizar catálogo si llega actualizado
            if (permisos) {
                permisosDisponibles.value = permisos;
            }
            modalEditar.value = true;
        });
}

function actualizarRol() {
    formularioRol.put(route('roles.update', formularioRol.id), {
        preserveScroll: true,
        onSuccess: () => {
            cerrarModalEditar();
        },
    });
}

function eliminarRol(id) {
    if (confirm('¿Está seguro de eliminar este rol?')) {
        router.delete(route('roles.destroy', id), {
            preserveState: true,
            onSuccess: () => {
                // Mostrar mensaje de éxito
            }
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioRol.reset();
    formularioRol.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioRol.reset();
    formularioRol.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    rolMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

<style scoped>
/* Los estilos usan las variables CSS del tema */
</style>

