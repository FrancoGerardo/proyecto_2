<template>
    <AppLayout title="Gestión de Salas">
        <template #header>
            <h2 class="tema-titulo">Gestión de Salas</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <!-- Botón Crear -->
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Salas</h3>
                    <button
                        v-if="tienePermiso('crear-salas')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Sala"
                    >
                        +
                    </button>
                </div>

                <!-- Tabla de Salas -->
                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Número</th>
                                <th>Categoría</th>
                                <th>Estado</th>
                                <th>Capacidad</th>
                                <th>Equipamiento</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="sala in salas.data" :key="sala.id">
                                <td class="text-center">{{ sala.id }}</td>
                                <td class="tema-texto font-bold text-center">{{ sala.numero }}</td>
                                <td class="text-center">{{ sala.categoria }}</td>
                                <td class="text-center">
                                    <span :class="getEstadoClass(sala.estado)">
                                        {{ sala.estado }}
                                    </span>
                                </td>
                                <td class="text-center">{{ sala.capacidad }}</td>
                                <td class="tema-texto-secundario">
                                    {{ sala.equipamiento || 'Sin especificar' }}
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-salas')"
                                        @click="abrirModalMostrar(sala.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar sala"
                                        aria-label="Mostrar sala"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-salas')"
                                        @click="abrirModalEditar(sala.id)"
                                        class="btn-tema mr-2"
                                        title="Editar sala"
                                        aria-label="Editar sala"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-salas')"
                                        @click="eliminarSala(sala.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar sala"
                                        aria-label="Eliminar sala"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Paginación -->
                <div class="mt-4" v-if="salas.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in salas.links"
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
            Crear Sala
        </template>

        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="sala-numero" value="Número" />
                    <TextInput id="sala-numero" type="number" class="mt-1 block w-full input-tema" v-model="formularioSala.numero" />
                    <InputError :message="formularioSala.errors.numero" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-categoria" value="Categoría" />
                    <TextInput id="sala-categoria" type="text" class="mt-1 block w-full input-tema" v-model="formularioSala.categoria" />
                    <InputError :message="formularioSala.errors.categoria" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-estado" value="Estado" />
                    <select id="sala-estado" class="mt-1 block w-full input-tema" v-model="formularioSala.estado">
                        <option v-for="estado in estadosDisponibles" :key="estado" :value="estado">{{ estado }}</option>
                    </select>
                    <InputError :message="formularioSala.errors.estado" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-capacidad" value="Capacidad" />
                    <TextInput id="sala-capacidad" type="number" class="mt-1 block w-full input-tema" v-model="formularioSala.capacidad" />
                    <InputError :message="formularioSala.errors.capacidad" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="sala-equipamiento" value="Equipamiento" />
                    <textarea id="sala-equipamiento" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSala.equipamiento"></textarea>
                    <InputError :message="formularioSala.errors.equipamiento" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="guardarSala" :disabled="formularioSala.processing">
                Guardar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Editar -->
    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>
            Editar Sala
        </template>

        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="sala-numero-edit" value="Número" />
                    <TextInput id="sala-numero-edit" type="number" class="mt-1 block w-full input-tema" v-model="formularioSala.numero" />
                    <InputError :message="formularioSala.errors.numero" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-categoria-edit" value="Categoría" />
                    <TextInput id="sala-categoria-edit" type="text" class="mt-1 block w-full input-tema" v-model="formularioSala.categoria" />
                    <InputError :message="formularioSala.errors.categoria" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-estado-edit" value="Estado" />
                    <select id="sala-estado-edit" class="mt-1 block w-full input-tema" v-model="formularioSala.estado">
                        <option v-for="estado in estadosDisponibles" :key="estado" :value="estado">{{ estado }}</option>
                    </select>
                    <InputError :message="formularioSala.errors.estado" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="sala-capacidad-edit" value="Capacidad" />
                    <TextInput id="sala-capacidad-edit" type="number" class="mt-1 block w-full input-tema" v-model="formularioSala.capacidad" />
                    <InputError :message="formularioSala.errors.capacidad" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="sala-equipamiento-edit" value="Equipamiento" />
                    <textarea id="sala-equipamiento-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioSala.equipamiento"></textarea>
                    <InputError :message="formularioSala.errors.equipamiento" class="mt-2" />
                </div>
            </div>
        </template>

        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">
                Cancelar
            </SecondaryButton>

            <PrimaryButton class="ms-3" @click="actualizarSala" :disabled="formularioSala.processing">
                Actualizar
            </PrimaryButton>
        </template>
    </DialogModal>

    <!-- Modal Mostrar -->
    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>
            Detalle de la Sala
        </template>

        <template #content>
            <div v-if="salaMostrada" class="space-y-3">
                <p><strong>ID:</strong> {{ salaMostrada.id }}</p>
                <p><strong>Número:</strong> {{ salaMostrada.numero }}</p>
                <p><strong>Categoría:</strong> {{ salaMostrada.categoria }}</p>
                <p><strong>Estado:</strong> {{ salaMostrada.estado }}</p>
                <p><strong>Capacidad:</strong> {{ salaMostrada.capacidad }}</p>
                <p><strong>Equipamiento:</strong> {{ salaMostrada.equipamiento || 'Sin especificar' }}</p>
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
import { Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import axios from 'axios';
import DialogModal from '@/Components/DialogModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    salas: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
console.log('Usuario actual:', pagina.props.auth.user);
console.log('Permisos del usuario:', pagina.props.auth.user?.permissions);

const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const salaMostrada = ref(null);
const estadosDisponibles = ['DISPONIBLE', 'OCUPADA', 'MANTENIMIENTO', 'INACTIVA'];

const formularioSala = useForm({
    id: null,
    numero: '',
    categoria: '',
    estado: 'DISPONIBLE',
    capacidad: 1,
    equipamiento: '',
});

function getEstadoClass(estado) {
    const clases = {
        'DISPONIBLE': 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs',
        'OCUPADA': 'bg-red-100 text-red-800 px-2 py-1 rounded text-xs',
        'MANTENIMIENTO': 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs',
        'INACTIVA': 'bg-gray-100 text-gray-800 px-2 py-1 rounded text-xs',
    };
    return clases[estado] || 'px-2 py-1 rounded text-xs';
}

function abrirModalCrear() {
    formularioSala.reset();
    formularioSala.clearErrors();
    formularioSala.estado = 'DISPONIBLE';
    formularioSala.capacidad = 1;
    modalCrear.value = true;
}

function abrirModalMostrar(id) {
    axios.get(route('salas.show', id))
        .then(response => {
            salaMostrada.value = response.data.sala;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('salas.edit', id))
        .then(response => {
            const { sala } = response.data;
            formularioSala.id = sala.id;
            formularioSala.numero = sala.numero;
            formularioSala.categoria = sala.categoria;
            formularioSala.estado = sala.estado;
            formularioSala.capacidad = sala.capacidad;
            formularioSala.equipamiento = sala.equipamiento;
            modalEditar.value = true;
        });
}

function guardarSala() {
    formularioSala.post(route('salas.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarSala() {
    formularioSala.put(route('salas.update', formularioSala.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarSala(id) {
    if (confirm('¿Está seguro de eliminar esta sala?')) {
        router.delete(route('salas.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioSala.reset();
    formularioSala.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioSala.reset();
    formularioSala.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    salaMostrada.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

<style scoped>
/* Los estilos usan las variables CSS del tema */
</style>

