<template>
    <AppLayout title="Gestión de Historiales Clínicos">
        <template #header>
            <h2 class="tema-titulo">Gestión de Historiales Clínicos</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Historiales Clínicos</h3>
                    <button
                        v-if="tienePermiso('crear-historiales-clinicos')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Historial Clínico"
                    >
                        +
                    </button>
                </div>

                <div class="overflow-x-auto">
                    <table class="tabla-tema w-full">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Alergias</th>
                                <th>Enfermedades Crónicas</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="historial in historiales.data" :key="historial.id">
                                <td class="text-center">{{ historial.id }}</td>
                                <td class="tema-texto font-bold">{{ historial.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="tema-texto-secundario">{{ historial.alergias || 'Sin alergias' }}</td>
                                <td class="tema-texto-secundario">{{ historial.enfermedades_cronicas || 'Sin enfermedades' }}</td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-historiales-clinicos')"
                                        @click="abrirModalMostrar(historial.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar historial"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-historiales-clinicos')"
                                        @click="abrirModalEditar(historial.id)"
                                        class="btn-tema mr-2"
                                        title="Editar historial"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-historiales-clinicos')"
                                        @click="eliminarHistorial(historial.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar historial"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="historiales.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in historiales.links"
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
        <template #title>Crear Historial Clínico</template>
        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="historial-cliente" value="Cliente *" />
                    <select id="historial-cliente" class="mt-1 block w-full input-tema" v-model="formularioHistorial.cliente_id">
                        <option value="">Seleccione...</option>
                        <option v-for="cliente in clientesDisponibles" :key="cliente.usuario_id" :value="cliente.usuario_id">
                            {{ cliente.usuario?.persona?.nombre_completo || cliente.usuario_id }}
                        </option>
                    </select>
                    <InputError :message="formularioHistorial.errors.cliente_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="historial-alergias" value="Alergias" />
                    <textarea id="historial-alergias" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.alergias"></textarea>
                    <InputError :message="formularioHistorial.errors.alergias" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="historial-enfermedades" value="Enfermedades Crónicas" />
                    <textarea id="historial-enfermedades" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.enfermedades_cronicas"></textarea>
                    <InputError :message="formularioHistorial.errors.enfermedades_cronicas" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="historial-medicamentos" value="Medicamentos Habituales" />
                    <textarea id="historial-medicamentos" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.medicamentos_habituales"></textarea>
                    <InputError :message="formularioHistorial.errors.medicamentos_habituales" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarHistorial" :disabled="formularioHistorial.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Historial Clínico</template>
        <template #content>
            <div class="space-y-4">
                <div>
                    <InputLabel for="historial-alergias-edit" value="Alergias" />
                    <textarea id="historial-alergias-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.alergias"></textarea>
                    <InputError :message="formularioHistorial.errors.alergias" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="historial-enfermedades-edit" value="Enfermedades Crónicas" />
                    <textarea id="historial-enfermedades-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.enfermedades_cronicas"></textarea>
                    <InputError :message="formularioHistorial.errors.enfermedades_cronicas" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="historial-medicamentos-edit" value="Medicamentos Habituales" />
                    <textarea id="historial-medicamentos-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioHistorial.medicamentos_habituales"></textarea>
                    <InputError :message="formularioHistorial.errors.medicamentos_habituales" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarHistorial" :disabled="formularioHistorial.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle del Historial Clínico</template>
        <template #content>
            <div v-if="historialMostrado" class="space-y-3">
                <p><strong>ID:</strong> {{ historialMostrado.id }}</p>
                <p><strong>Cliente:</strong> {{ historialMostrado.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</p>
                <p><strong>Alergias:</strong> {{ historialMostrado.alergias || 'Sin alergias' }}</p>
                <p><strong>Enfermedades Crónicas:</strong> {{ historialMostrado.enfermedades_cronicas || 'Sin enfermedades' }}</p>
                <p><strong>Medicamentos Habituales:</strong> {{ historialMostrado.medicamentos_habituales || 'Sin medicamentos' }}</p>
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
    historiales: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const historialMostrado = ref(null);
const clientesDisponibles = ref([]);

const formularioHistorial = useForm({
    id: null,
    cliente_id: '',
    alergias: '',
    enfermedades_cronicas: '',
    medicamentos_habituales: '',
});

function abrirModalCrear() {
    formularioHistorial.reset();
    formularioHistorial.clearErrors();
    axios.get(route('historiales-clinicos.create'))
        .then(response => {
            clientesDisponibles.value = response.data.clientes || [];
            modalCrear.value = true;
        });
}

function abrirModalMostrar(id) {
    axios.get(route('historiales-clinicos.show', id))
        .then(response => {
            historialMostrado.value = response.data.historial;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('historiales-clinicos.edit', id))
        .then(response => {
            const { historial } = response.data;
            formularioHistorial.id = historial.id;
            formularioHistorial.alergias = historial.alergias || '';
            formularioHistorial.enfermedades_cronicas = historial.enfermedades_cronicas || '';
            formularioHistorial.medicamentos_habituales = historial.medicamentos_habituales || '';
            modalEditar.value = true;
        });
}

function guardarHistorial() {
    formularioHistorial.post(route('historiales-clinicos.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarHistorial() {
    formularioHistorial.put(route('historiales-clinicos.update', formularioHistorial.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarHistorial(id) {
    if (confirm('¿Está seguro de eliminar este historial clínico?')) {
        router.delete(route('historiales-clinicos.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioHistorial.reset();
    formularioHistorial.clearErrors();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioHistorial.reset();
    formularioHistorial.clearErrors();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    historialMostrado.value = null;
};

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
});
</script>

