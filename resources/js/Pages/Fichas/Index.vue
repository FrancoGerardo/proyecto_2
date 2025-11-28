<template>
    <AppLayout title="Gestión de Fichas">
        <template #header>
            <h2 class="tema-titulo">Gestión de Fichas</h2>
        </template>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-6">
            <div class="card-tema">
                <div class="mb-4 flex justify-between items-center">
                    <h3 class="tema-titulo">Lista de Fichas</h3>
                    <button
                        v-if="tienePermiso('crear-fichas')"
                        @click="abrirModalCrear"
                        class="btn-tema flex items-center justify-center text-lg font-bold"
                        title="Crear Ficha"
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
                                <th>Servicio</th>
                                <th>Médico</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="ficha in fichas.data" :key="ficha.id">
                                <td class="text-center">{{ ficha.id }}</td>
                                <td class="tema-texto">{{ ficha.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ ficha.servicio?.nombre || 'N/A' }}</td>
                                <td class="text-center">{{ ficha.medico?.usuario?.persona?.nombre_completo || 'N/A' }}</td>
                                <td class="text-center">{{ ficha.fecha }}</td>
                                <td class="text-center">{{ ficha.hora }}</td>
                                <td class="text-center">
                                    <span :class="getEstadoClass(ficha.estado)">
                                        {{ ficha.estado }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button
                                        v-if="tienePermiso('mostrar-fichas')"
                                        @click="abrirModalMostrar(ficha.id)"
                                        class="btn-tema-secundario mr-2"
                                        title="Mostrar ficha"
                                    >
                                        👁️
                                    </button>
                                    <button
                                        v-if="tienePermiso('editar-fichas')"
                                        @click="abrirModalEditar(ficha.id)"
                                        class="btn-tema mr-2"
                                        title="Editar ficha"
                                    >
                                        ✏️
                                    </button>
                                    <button
                                        v-if="tienePermiso('eliminar-fichas')"
                                        @click="eliminarFicha(ficha.id)"
                                        class="btn-tema-secundario"
                                        title="Eliminar ficha"
                                    >
                                        🗑️
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="mt-4" v-if="fichas.links">
                    <div class="flex justify-center">
                        <Link
                            v-for="link in fichas.links"
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
        <template #title>Crear Ficha</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="ficha-cliente" value="Cliente *" />
                    <select id="ficha-cliente" class="mt-1 block w-full input-tema" v-model="formularioFicha.cliente_id">
                        <option value="">Seleccione...</option>
                        <option v-for="cliente in clientesDisponibles" :key="cliente.usuario_id" :value="cliente.usuario_id">
                            {{ cliente.usuario?.persona?.nombre_completo || cliente.usuario_id }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.cliente_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-categoria" value="Tipo de Servicio *" />
                    <select
                        id="ficha-categoria"
                        class="mt-1 block w-full input-tema"
                        v-model="categoriaSeleccionada"
                    >
                        <option value="">Seleccione...</option>
                        <option
                            v-for="categoria in categoriasServicio"
                            :key="categoria.valor"
                            :value="categoria.valor"
                        >
                            {{ categoria.etiqueta }}
                        </option>
                    </select>
                </div>

                <template v-if="categoriaSeleccionada === 'ESPECIALIDAD'">
                    <div class="md:col-span-2">
                        <InputLabel for="ficha-especialidad" value="Especialidad *" />
                        <select
                            id="ficha-especialidad"
                            class="mt-1 block w-full input-tema"
                            v-model="especialidadSeleccionada"
                        >
                            <option value="">Seleccione...</option>
                            <option
                                v-for="especialidad in especialidadesConMedicos"
                                :key="especialidad.id"
                                :value="especialidad.id"
                            >
                                {{ especialidad.nombre }}
                            </option>
                        </select>
                        <p v-if="especialidadesConMedicos.length === 0" class="text-xs tema-texto-secundario mt-1">
                            No existen especialidades con profesionales asignados.
                        </p>
                        <InputError :message="formularioFicha.errors.servicio_id" class="mt-2" />
                    </div>
                </template>

                <template v-else-if="categoriaSeleccionada">
                    <div>
                        <InputLabel for="ficha-servicio" value="Servicio *" />
                        <select
                            id="ficha-servicio"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.servicio_id"
                        >
                            <option value="">Seleccione...</option>
                            <option
                                v-for="servicio in serviciosFiltrados"
                                :key="servicio.id"
                                :value="servicio.id"
                            >
                                {{ servicio.nombre }}
                            </option>
                        </select>
                        <InputError :message="formularioFicha.errors.servicio_id" class="mt-2" />
                    </div>
                </template>

                <div v-if="precioServicio !== null" class="md:col-span-2">
                    <InputLabel value="Costo estimado" />
                    <div class="mt-1 w-full input-tema bg-gray-100">
                        {{ formatearMoneda(precioServicio) }}
                    </div>
                </div>

                <div v-if="categoriaSeleccionada === 'ESPECIALIDAD'">
                    <InputLabel for="ficha-medico-especialidad" value="Profesional *" />
                    <select
                        id="ficha-medico-especialidad"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.medico_id"
                    >
                        <option value="">Seleccione...</option>
                        <option
                            v-for="medico in medicosFiltrados"
                            :key="medico.usuario_id"
                            :value="medico.usuario_id"
                        >
                            {{ obtenerNombreCompletoMedico(medico) }}
                        </option>
                    </select>
                    <p v-if="medicosFiltrados.length === 0" class="text-xs tema-texto-secundario mt-1">
                        Seleccione primero una especialidad válida.
                    </p>
                    <InputError :message="formularioFicha.errors.medico_id" class="mt-2" />
                </div>

                <div v-else>
                    <InputLabel for="ficha-medico" value="Médico *" />
                    <select
                        id="ficha-medico"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.medico_id"
                    >
                        <option value="">Seleccione...</option>
                        <option
                            v-for="medico in medicosFiltrados"
                            :key="medico.usuario_id"
                            :value="medico.usuario_id"
                        >
                            {{ obtenerNombreCompletoMedico(medico) }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.medico_id" class="mt-2" />
                </div>

                <div v-if="categoriaSeleccionada !== 'ESPECIALIDAD'">
                    <InputLabel for="ficha-sala" value="Sala" />
                    <select
                        id="ficha-sala"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.sala_id"
                    >
                        <option value="">Sin sala</option>
                        <option
                            v-for="sala in salasFiltradas"
                            :key="sala.id"
                            :value="sala.id"
                        >
                            {{ sala.numero }} - {{ sala.categoria }}
                        </option>
                    </select>
                    <p v-if="salasFiltradas.length === 0" class="text-xs tema-texto-secundario mt-1">
                        No hay salas disponibles para esta categoría.
                    </p>
                    <InputError :message="formularioFicha.errors.sala_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-fecha" value="Fecha *" />
                    <TextInput id="ficha-fecha" type="date" class="mt-1 block w-full input-tema" v-model="formularioFicha.fecha" />
                    <InputError :message="formularioFicha.errors.fecha" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-hora" value="Hora *" />
                    <template v-if="horasDisponibles.length > 0">
                        <select
                            id="ficha-hora"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.hora"
                        >
                            <option value="">Seleccione...</option>
                            <option v-for="hora in horasDisponibles" :key="hora" :value="hora">
                                {{ hora }}
                            </option>
                        </select>
                        <p class="text-xs tema-texto-secundario mt-1">
                            {{ mensajeHorarios || 'Seleccione uno de los horarios disponibles.' }}
                        </p>
                    </template>
                    <template v-else>
                        <TextInput
                            id="ficha-hora"
                            type="time"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.hora"
                        />
                        <p v-if="mensajeHorarios" class="text-xs tema-texto-secundario mt-1">
                            {{ mensajeHorarios }}
                        </p>
                    </template>
                    <InputError :message="formularioFicha.errors.hora" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-estado" value="Estado" />
                    <select id="ficha-estado" class="mt-1 block w-full input-tema" v-model="formularioFicha.estado">
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="CONFIRMADA">Confirmada</option>
                        <option value="ATENDIDA">Atendida</option>
                        <option value="CANCELADA">Cancelada</option>
                    </select>
                    <InputError :message="formularioFicha.errors.estado" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="ficha-motivo" value="Motivo de Consulta" />
                    <textarea id="ficha-motivo" class="mt-1 block w-full input-tema" rows="3" v-model="formularioFicha.motivo_consulta"></textarea>
                    <InputError :message="formularioFicha.errors.motivo_consulta" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalCrear">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="guardarFicha" :disabled="formularioFicha.processing">Guardar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalEditar" @close="cerrarModalEditar">
        <template #title>Editar Ficha</template>
        <template #content>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <InputLabel for="ficha-cliente-edit" value="Cliente *" />
                    <select id="ficha-cliente-edit" class="mt-1 block w-full input-tema" v-model="formularioFicha.cliente_id">
                        <option v-for="cliente in clientesDisponibles" :key="cliente.usuario_id" :value="cliente.usuario_id">
                            {{ cliente.usuario?.persona?.nombre_completo || cliente.usuario_id }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.cliente_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-categoria-edit" value="Tipo de Servicio *" />
                    <select
                        id="ficha-categoria-edit"
                        class="mt-1 block w-full input-tema"
                        v-model="categoriaSeleccionada"
                    >
                        <option
                            v-for="categoria in categoriasServicio"
                            :key="categoria.valor"
                            :value="categoria.valor"
                        >
                            {{ categoria.etiqueta }}
                        </option>
                    </select>
                </div>

                <template v-if="categoriaSeleccionada === 'ESPECIALIDAD'">
                    <div class="md:col-span-2">
                        <InputLabel for="ficha-especialidad-edit" value="Especialidad *" />
                        <select
                            id="ficha-especialidad-edit"
                            class="mt-1 block w-full input-tema"
                            v-model="especialidadSeleccionada"
                        >
                            <option v-for="especialidad in especialidadesConMedicos" :key="especialidad.id" :value="especialidad.id">
                                {{ especialidad.nombre }}
                            </option>
                        </select>
                        <InputError :message="formularioFicha.errors.servicio_id" class="mt-2" />
                    </div>
                </template>

                <template v-else-if="categoriaSeleccionada">
                    <div>
                        <InputLabel for="ficha-servicio-edit" value="Servicio *" />
                        <select
                            id="ficha-servicio-edit"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.servicio_id"
                        >
                            <option v-for="servicio in serviciosFiltrados" :key="servicio.id" :value="servicio.id">
                                {{ servicio.nombre }}
                            </option>
                        </select>
                        <InputError :message="formularioFicha.errors.servicio_id" class="mt-2" />
                    </div>
                </template>

                <div v-if="precioServicio !== null" class="md:col-span-2">
                    <InputLabel value="Costo estimado" />
                    <div class="mt-1 w-full input-tema bg-gray-100">
                        {{ formatearMoneda(precioServicio) }}
                    </div>
                </div>

                <div v-if="categoriaSeleccionada === 'ESPECIALIDAD'">
                    <InputLabel for="ficha-medico-edit" value="Profesional *" />
                    <select
                        id="ficha-medico-edit"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.medico_id"
                    >
                        <option v-for="medico in medicosFiltrados" :key="medico.usuario_id" :value="medico.usuario_id">
                            {{ obtenerNombreCompletoMedico(medico) }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.medico_id" class="mt-2" />
                </div>

                <div v-else>
                    <InputLabel for="ficha-medico-edit" value="Médico *" />
                    <select
                        id="ficha-medico-edit"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.medico_id"
                    >
                        <option v-for="medico in medicosFiltrados" :key="medico.usuario_id" :value="medico.usuario_id">
                            {{ obtenerNombreCompletoMedico(medico) }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.medico_id" class="mt-2" />
                </div>

                <div v-if="categoriaSeleccionada !== 'ESPECIALIDAD'">
                    <InputLabel for="ficha-sala-edit" value="Sala" />
                    <select
                        id="ficha-sala-edit"
                        class="mt-1 block w-full input-tema"
                        v-model="formularioFicha.sala_id"
                    >
                        <option value="">Sin sala</option>
                        <option v-for="sala in salasFiltradas" :key="sala.id" :value="sala.id">
                            {{ sala.numero }} - {{ sala.categoria }}
                        </option>
                    </select>
                    <InputError :message="formularioFicha.errors.sala_id" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-fecha-edit" value="Fecha *" />
                    <TextInput id="ficha-fecha-edit" type="date" class="mt-1 block w-full input-tema" v-model="formularioFicha.fecha" />
                    <InputError :message="formularioFicha.errors.fecha" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-hora-edit" value="Hora *" />
                    <template v-if="horasDisponibles.length > 0">
                        <select
                            id="ficha-hora-edit"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.hora"
                        >
                            <option value="">Seleccione...</option>
                            <option v-for="hora in horasDisponibles" :key="`edit-${hora}`" :value="hora">
                                {{ hora }}
                            </option>
                        </select>
                        <p class="text-xs tema-texto-secundario mt-1">
                            {{ mensajeHorarios || 'Seleccione uno de los horarios disponibles.' }}
                        </p>
                    </template>
                    <template v-else>
                        <TextInput
                            id="ficha-hora-edit"
                            type="time"
                            class="mt-1 block w-full input-tema"
                            v-model="formularioFicha.hora"
                        />
                        <p v-if="mensajeHorarios" class="text-xs tema-texto-secundario mt-1">
                            {{ mensajeHorarios }}
                        </p>
                    </template>
                    <InputError :message="formularioFicha.errors.hora" class="mt-2" />
                </div>
                <div>
                    <InputLabel for="ficha-estado-edit" value="Estado *" />
                    <select id="ficha-estado-edit" class="mt-1 block w-full input-tema" v-model="formularioFicha.estado">
                        <option value="PENDIENTE">Pendiente</option>
                        <option value="CONFIRMADA">Confirmada</option>
                        <option value="ATENDIDA">Atendida</option>
                        <option value="CANCELADA">Cancelada</option>
                    </select>
                    <InputError :message="formularioFicha.errors.estado" class="mt-2" />
                </div>
                <div class="md:col-span-2">
                    <InputLabel for="ficha-motivo-edit" value="Motivo de Consulta" />
                    <textarea id="ficha-motivo-edit" class="mt-1 block w-full input-tema" rows="3" v-model="formularioFicha.motivo_consulta"></textarea>
                    <InputError :message="formularioFicha.errors.motivo_consulta" class="mt-2" />
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="cerrarModalEditar">Cancelar</SecondaryButton>
            <PrimaryButton class="ms-3" @click="actualizarFicha" :disabled="formularioFicha.processing">Actualizar</PrimaryButton>
        </template>
    </DialogModal>

    <DialogModal :show="modalMostrar" @close="cerrarModalMostrar">
        <template #title>Detalle de la Ficha</template>
        <template #content>
            <div v-if="fichaMostrada" class="space-y-3">
                <p><strong>ID:</strong> {{ fichaMostrada.id }}</p>
                <p><strong>Cliente:</strong> {{ fichaMostrada.cliente?.usuario?.persona?.nombre_completo || 'N/A' }}</p>
                <p><strong>Servicio:</strong> {{ fichaMostrada.servicio?.nombre || 'N/A' }}</p>
                <p><strong>Médico:</strong> {{ fichaMostrada.medico?.usuario?.persona?.nombre_completo || 'N/A' }}</p>
                <p><strong>Sala:</strong> {{ fichaMostrada.sala?.numero || 'Sin sala' }}</p>
                <p><strong>Fecha:</strong> {{ fichaMostrada.fecha }}</p>
                <p><strong>Hora:</strong> {{ fichaMostrada.hora }}</p>
                <p><strong>Estado:</strong> {{ fichaMostrada.estado }}</p>
                <p><strong>Motivo:</strong> {{ fichaMostrada.motivo_consulta || 'Sin motivo' }}</p>
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
    fichas: Object,
    contadorVisitas: Number,
});

const pagina = usePage();
const contadorVisitas = ref(props.contadorVisitas || 0);
const modalCrear = ref(false);
const modalEditar = ref(false);
const modalMostrar = ref(false);
const fichaMostrada = ref(null);
const clientesDisponibles = ref([]);
const serviciosDisponibles = ref([]);
const medicosDisponibles = ref([]);
const salasDisponibles = ref([]);
const categoriasServicio = [
    { valor: 'INTERNACION', etiqueta: 'Internación' },
    { valor: 'ESPECIALIDAD', etiqueta: 'Especialidad' },
    { valor: 'ENFERMERIA', etiqueta: 'Enfermería' },
];
const categoriaSeleccionada = ref('');
const especialidadSeleccionada = ref('');
const serviciosFiltrados = ref([]);
const especialidadesConMedicos = ref([]);
const medicosFiltrados = ref([]);
const salasFiltradas = ref([]);
const precioServicio = ref(null);
const horasDisponibles = ref([]);
const mensajeHorarios = ref('');
const cargandoHorarios = ref(false);
const bloqueandoReacciones = ref(false);

const formularioFicha = useForm({
    id: null,
    cliente_id: '',
    servicio_id: '',
    medico_id: '',
    sala_id: '',
    fecha: '',
    hora: '',
    estado: 'PENDIENTE',
    motivo_consulta: '',
});

function getEstadoClass(estado) {
    const clases = {
        'PENDIENTE': 'bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs',
        'CONFIRMADA': 'bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs',
        'ATENDIDA': 'bg-green-100 text-green-800 px-2 py-1 rounded text-xs',
        'CANCELADA': 'bg-red-100 text-red-800 px-2 py-1 rounded text-xs',
    };
    return clases[estado] || 'px-2 py-1 rounded text-xs';
}

function formatearMoneda(valor) {
    if (valor === null || valor === undefined || valor === '') {
        return 'Bs 0.00';
    }
    return new Intl.NumberFormat('es-BO', { style: 'currency', currency: 'BOB' }).format(Number(valor));
}

function obtenerNombreCompletoMedico(medico) {
    const persona = medico?.usuario?.persona;
    if (persona) {
        const nombre = persona.nombre || '';
        const apellidos = persona.apellidos || persona.nombre_completo || '';
        const completo = `${nombre} ${apellidos}`.trim();
        if (completo) {
            return completo;
        }
    }
    return medico?.usuario?.name || medico?.usuario_id || 'Profesional sin nombre';
}

function limpiarSeleccionesSecundarias() {
    formularioFicha.servicio_id = '';
    formularioFicha.medico_id = '';
    formularioFicha.sala_id = '';
    especialidadSeleccionada.value = '';
    precioServicio.value = null;
    limpiarHorariosDisponibles();
}

function construirEspecialidadesConProfesionales() {
    const mapa = new Map();
    serviciosDisponibles.value
        .filter((servicio) => servicio.categoria === 'ESPECIALIDAD' && servicio.especialidad)
        .forEach((servicio) => {
            if (!mapa.has(servicio.especialidad.id)) {
                mapa.set(servicio.especialidad.id, {
                    id: servicio.especialidad.id,
                    nombre: servicio.especialidad.nombre,
                    servicio_id: servicio.id,
                    costo: Number(servicio.costo),
                    medicos: servicio.medicos || [],
                });
            }
        });
    especialidadesConMedicos.value = Array.from(mapa.values());
}

function aplicarCambioCategoria() {
    limpiarSeleccionesSecundarias();
    serviciosFiltrados.value = [];
    especialidadesConMedicos.value = [];
    medicosFiltrados.value = medicosDisponibles.value;
    salasFiltradas.value = salasDisponibles.value;

    if (categoriaSeleccionada.value === 'ESPECIALIDAD') {
        construirEspecialidadesConProfesionales();
        medicosFiltrados.value = [];
        salasFiltradas.value = [];
    } else if (categoriaSeleccionada.value) {
        serviciosFiltrados.value = serviciosDisponibles.value.filter(
            (servicio) => servicio.categoria === categoriaSeleccionada.value,
        );
        actualizarSalasSegunCategoria();
    }
}

function sincronizarServicioDesdeEspecialidad() {
    formularioFicha.servicio_id = '';
    precioServicio.value = null;
    medicosFiltrados.value = [];

    if (!especialidadSeleccionada.value) {
        return;
    }

    if (especialidadesConMedicos.value.length === 0) {
        construirEspecialidadesConProfesionales();
    }

    const especialidad = especialidadesConMedicos.value.find(
        (item) => item.id === especialidadSeleccionada.value,
    );

    if (!especialidad) {
        return;
    }

    formularioFicha.servicio_id = especialidad.servicio_id;
    precioServicio.value = especialidad.costo ?? null;
    medicosFiltrados.value = especialidad.medicos?.length
        ? especialidad.medicos
        : medicosDisponibles.value;

    cargarHorariosDisponibles();
}

function actualizarMedicosSegunServicio() {
    if (!formularioFicha.servicio_id) {
        precioServicio.value = null;
        medicosFiltrados.value = medicosDisponibles.value;
        return;
    }

    const servicio = serviciosDisponibles.value.find(
        (item) => item.id === formularioFicha.servicio_id,
    );

    if (!servicio) {
        precioServicio.value = null;
        medicosFiltrados.value = medicosDisponibles.value;
        return;
    }

    precioServicio.value = servicio.costo ?? null;
    medicosFiltrados.value = servicio.medicos?.length ? servicio.medicos : medicosDisponibles.value;
    actualizarSalasSegunCategoria();
    cargarHorariosDisponibles();
}

function actualizarSalasSegunCategoria() {
    if (categoriaSeleccionada.value === 'ESPECIALIDAD') {
        salasFiltradas.value = [];
        formularioFicha.sala_id = '';
        return;
    }

    if (!categoriaSeleccionada.value) {
        salasFiltradas.value = salasDisponibles.value;
        return;
    }

    salasFiltradas.value = salasDisponibles.value.filter(
        (sala) => sala.categoria?.toUpperCase() === categoriaSeleccionada.value,
    );
}

function limpiarHorariosDisponibles() {
    horasDisponibles.value = [];
    mensajeHorarios.value = '';
}

function cargarHorariosDisponibles() {
    limpiarHorariosDisponibles();
    if (!formularioFicha.medico_id || !formularioFicha.fecha || !formularioFicha.servicio_id) {
        return;
    }

    cargandoHorarios.value = true;
    axios.get(route('fichas.horarios-disponibles'), {
        params: {
            medico_id: formularioFicha.medico_id,
            servicio_id: formularioFicha.servicio_id,
            fecha: formularioFicha.fecha,
        },
    })
        .then((response) => {
            horasDisponibles.value = response.data.horas || [];
            if (horasDisponibles.value.length === 0) {
                mensajeHorarios.value = 'Sin horarios disponibles para la fecha indicada.';
            } else if (!horasDisponibles.value.includes(formularioFicha.hora)) {
                formularioFicha.hora = '';
            }
        })
        .catch(() => {
            mensajeHorarios.value = 'No se pudo obtener la disponibilidad.';
        })
        .finally(() => {
            cargandoHorarios.value = false;
        });
}

function asignarCatalogos(data) {
    clientesDisponibles.value = data.clientes || [];
    serviciosDisponibles.value = (data.servicios || []).map((servicio) => ({
        ...servicio,
        medicos: servicio.medicos || [],
    }));
    medicosDisponibles.value = data.medicos || [];
    salasDisponibles.value = data.salas || [];
}

function prepararFormularioParaNuevoRegistro() {
    bloqueandoReacciones.value = true;
    categoriaSeleccionada.value = '';
    especialidadSeleccionada.value = '';
    serviciosFiltrados.value = [];
    especialidadesConMedicos.value = [];
    medicosFiltrados.value = medicosDisponibles.value;
    salasFiltradas.value = salasDisponibles.value;
    precioServicio.value = null;
    limpiarHorariosDisponibles();
    bloqueandoReacciones.value = false;
}

function abrirModalCrear() {
    formularioFicha.reset();
    formularioFicha.clearErrors();
    formularioFicha.estado = 'PENDIENTE';
    axios.get(route('fichas.create'))
        .then((response) => {
            asignarCatalogos(response.data);
            prepararFormularioParaNuevoRegistro();
            modalCrear.value = true;
        });
}

function abrirModalMostrar(id) {
    axios.get(route('fichas.show', id))
        .then(response => {
            fichaMostrada.value = response.data.ficha;
            modalMostrar.value = true;
        });
}

function abrirModalEditar(id) {
    axios.get(route('fichas.edit', id))
        .then((response) => {
            const { ficha, ...catalogos } = response.data;
            formularioFicha.id = ficha.id;
            formularioFicha.cliente_id = ficha.cliente_id;
            formularioFicha.servicio_id = ficha.servicio_id;
            formularioFicha.medico_id = ficha.medico_id;
            formularioFicha.sala_id = ficha.sala_id || '';
            formularioFicha.fecha = ficha.fecha;
            formularioFicha.hora = ficha.hora;
            formularioFicha.estado = ficha.estado;
            formularioFicha.motivo_consulta = ficha.motivo_consulta || '';
            asignarCatalogos(catalogos);

            bloqueandoReacciones.value = true;
            categoriaSeleccionada.value = ficha.servicio?.categoria || '';
            especialidadSeleccionada.value = categoriaSeleccionada.value === 'ESPECIALIDAD'
                ? (ficha.servicio?.especialidad_id || '')
                : '';
            bloqueandoReacciones.value = false;

            if (categoriaSeleccionada.value === 'ESPECIALIDAD') {
                construirEspecialidadesConProfesionales();
                if (!especialidadSeleccionada.value && ficha.servicio?.especialidad_id) {
                    especialidadSeleccionada.value = ficha.servicio.especialidad_id;
                }
                sincronizarServicioDesdeEspecialidad();
                salasFiltradas.value = [];
            } else if (categoriaSeleccionada.value) {
                serviciosFiltrados.value = serviciosDisponibles.value.filter(
                    (servicio) => servicio.categoria === categoriaSeleccionada.value,
                );
                actualizarMedicosSegunServicio();
                actualizarSalasSegunCategoria();
            } else {
                medicosFiltrados.value = medicosDisponibles.value;
                salasFiltradas.value = salasDisponibles.value;
            }

            cargarHorariosDisponibles();
            modalEditar.value = true;
        });
}

function guardarFicha() {
    formularioFicha.post(route('fichas.store'), {
        preserveScroll: true,
        onSuccess: cerrarModalCrear,
    });
}

function actualizarFicha() {
    formularioFicha.put(route('fichas.update', formularioFicha.id), {
        preserveScroll: true,
        onSuccess: cerrarModalEditar,
    });
}

function eliminarFicha(id) {
    if (confirm('¿Está seguro de eliminar esta ficha?')) {
        router.delete(route('fichas.destroy', id), {
            preserveState: true,
        });
    }
}

const cerrarModalCrear = () => {
    modalCrear.value = false;
    formularioFicha.reset();
    formularioFicha.clearErrors();
    prepararFormularioParaNuevoRegistro();
};

const cerrarModalEditar = () => {
    modalEditar.value = false;
    formularioFicha.reset();
    formularioFicha.clearErrors();
    prepararFormularioParaNuevoRegistro();
};

const cerrarModalMostrar = () => {
    modalMostrar.value = false;
    fichaMostrada.value = null;
};

watch(categoriaSeleccionada, () => {
    if (bloqueandoReacciones.value) {
        return;
    }
    aplicarCambioCategoria();
});

watch(especialidadSeleccionada, () => {
    if (bloqueandoReacciones.value) {
        return;
    }
    sincronizarServicioDesdeEspecialidad();
});

watch(() => formularioFicha.servicio_id, () => {
    if (bloqueandoReacciones.value || categoriaSeleccionada.value === 'ESPECIALIDAD') {
        return;
    }
    actualizarMedicosSegunServicio();
});

watch(() => formularioFicha.medico_id, () => {
    if (bloqueandoReacciones.value) {
        return;
    }
    cargarHorariosDisponibles();
});

watch(() => formularioFicha.fecha, () => {
    if (bloqueandoReacciones.value) {
        return;
    }
    cargarHorariosDisponibles();
});

onMounted(() => {
    contadorVisitas.value = props.contadorVisitas || 0;
    medicosFiltrados.value = medicosDisponibles.value;
    salasFiltradas.value = salasDisponibles.value;
});
</script>

