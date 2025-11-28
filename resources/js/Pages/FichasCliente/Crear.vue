<script setup>
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import axios from 'axios';
import { ref, watch } from 'vue';

const props = defineProps({
    servicios: Array,
    especialidades: Array,
    servicio_id: String,
});

const pasoActual = ref(1); // 1: Servicio, 2: Médico, 3: Horario, 4: Confirmar
const tipoSeleccion = ref('servicio'); // 'servicio' o 'especialidad'
const servicioSeleccionado = ref(null);
const especialidadSeleccionada = ref(null);
const medicosDisponibles = ref([]);
const medicoSeleccionado = ref(null);
const fechaSeleccionada = ref('');
const horariosDisponibles = ref([]);
const horaSeleccionada = ref('');
const cargandoMedicos = ref(false);
const cargandoHorarios = ref(false);

const formulario = useForm({
    servicio_id: props.servicio_id || '',
    medico_id: '',
    fecha: '',
    hora: '',
    motivo_consulta: '',
});

// Si viene servicio_id, cargar automáticamente
if (props.servicio_id) {
    servicioSeleccionado.value = props.servicios.find(s => s.id === props.servicio_id);
    if (servicioSeleccionado.value) {
        formulario.servicio_id = props.servicio_id;
        tipoSeleccion.value = 'servicio';
        pasoActual.value = 2;
        cargarMedicos();
    }
}

function seleccionarServicio(servicio) {
    servicioSeleccionado.value = servicio;
    especialidadSeleccionada.value = null;
    formulario.servicio_id = servicio.id;
    tipoSeleccion.value = 'servicio';
    pasoActual.value = 2;
    cargarMedicos();
}

function seleccionarEspecialidad(especialidad) {
    especialidadSeleccionada.value = especialidad;
    servicioSeleccionado.value = null;
    formulario.servicio_id = '';
    tipoSeleccion.value = 'especialidad';
    pasoActual.value = 2;
    cargarMedicos();
}

function cargarMedicos() {
    cargandoMedicos.value = true;
    const params = tipoSeleccion.value === 'servicio' 
        ? { servicio_id: formulario.servicio_id }
        : { especialidad_id: especialidadSeleccionada.value.id };

    axios.get(route('cliente.fichas.medicos'), { params })
        .then(response => {
            medicosDisponibles.value = response.data.medicos || [];
            
            // Si viene servicio_id del backend (cuando se selecciona especialidad), asignarlo
            if (response.data.servicio_id && !formulario.servicio_id) {
                formulario.servicio_id = response.data.servicio_id;
                
                // Buscar el servicio en la lista para mostrarlo
                const servicioEncontrado = props.servicios.find(s => s.id === response.data.servicio_id);
                if (servicioEncontrado) {
                    servicioSeleccionado.value = servicioEncontrado;
                }
            }
        })
        .catch(error => {
            console.error('Error al cargar médicos:', error);
            medicosDisponibles.value = [];
        })
        .finally(() => {
            cargandoMedicos.value = false;
        });
}

function seleccionarMedico(medico) {
    medicoSeleccionado.value = medico;
    formulario.medico_id = medico.usuario_id;
    pasoActual.value = 3;
    horariosDisponibles.value = [];
    horaSeleccionada.value = '';
}

watch(() => formulario.fecha, (nuevaFecha) => {
    if (nuevaFecha && medicoSeleccionado.value) {
        cargarHorarios();
    }
});

function cargarHorarios() {
    if (!formulario.fecha || !medicoSeleccionado.value) return;

    cargandoHorarios.value = true;
    axios.get(route('cliente.fichas.horarios'), {
        params: {
            medico_id: formulario.medico_id,
            fecha: formulario.fecha,
        }
    })
        .then(response => {
            horariosDisponibles.value = response.data.horas || [];
        })
        .catch(error => {
            console.error('Error al cargar horarios:', error);
            horariosDisponibles.value = [];
        })
        .finally(() => {
            cargandoHorarios.value = false;
        });
}

function seleccionarHora(hora) {
    horaSeleccionada.value = hora;
    formulario.hora = hora;
    pasoActual.value = 4;
}

function confirmarYGuardar() {
    formulario.post(route('cliente.fichas.store'), {
        onSuccess: () => {
            // Redirigirá automáticamente a la pantalla de pago
        },
    });
}

function volverAtras() {
    if (pasoActual.value > 1) {
        pasoActual.value--;
        if (pasoActual.value === 1) {
            servicioSeleccionado.value = null;
            especialidadSeleccionada.value = null;
            medicoSeleccionado.value = null;
            fechaSeleccionada.value = '';
            horaSeleccionada.value = '';
        } else if (pasoActual.value === 2) {
            medicoSeleccionado.value = null;
            fechaSeleccionada.value = '';
            horaSeleccionada.value = '';
        } else if (pasoActual.value === 3) {
            fechaSeleccionada.value = '';
            horaSeleccionada.value = '';
        }
    }
}
</script>

<template>
    <AppLayout title="Generar Ficha">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Generar Nueva Ficha
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Indicador de Pasos -->
                <div class="mb-8">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', pasoActual >= 1 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600']">
                                1
                            </div>
                            <span class="text-sm font-medium">Servicio</span>
                        </div>
                        <div class="flex-1 h-1 mx-2" :class="pasoActual >= 2 ? 'bg-indigo-600' : 'bg-gray-300'"></div>
                        <div class="flex items-center space-x-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', pasoActual >= 2 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600']">
                                2
                            </div>
                            <span class="text-sm font-medium">Médico</span>
                        </div>
                        <div class="flex-1 h-1 mx-2" :class="pasoActual >= 3 ? 'bg-indigo-600' : 'bg-gray-300'"></div>
                        <div class="flex items-center space-x-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', pasoActual >= 3 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600']">
                                3
                            </div>
                            <span class="text-sm font-medium">Horario</span>
                        </div>
                        <div class="flex-1 h-1 mx-2" :class="pasoActual >= 4 ? 'bg-indigo-600' : 'bg-gray-300'"></div>
                        <div class="flex items-center space-x-2">
                            <div :class="['w-8 h-8 rounded-full flex items-center justify-center', pasoActual >= 4 ? 'bg-indigo-600 text-white' : 'bg-gray-300 text-gray-600']">
                                4
                            </div>
                            <span class="text-sm font-medium">Confirmar</span>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md p-6">
                    <!-- Paso 1: Seleccionar Servicio o Especialidad -->
                    <div v-if="pasoActual === 1">
                        <h3 class="text-xl font-bold mb-4">Selecciona un Servicio o Especialidad</h3>
                        
                        <div class="mb-6">
                            <div class="flex space-x-4 mb-4">
                                <button
                                    @click="tipoSeleccion = 'servicio'"
                                    :class="['px-4 py-2 rounded-lg font-medium', tipoSeleccion === 'servicio' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700']"
                                >
                                    Servicios
                                </button>
                                <button
                                    @click="tipoSeleccion = 'especialidad'"
                                    :class="['px-4 py-2 rounded-lg font-medium', tipoSeleccion === 'especialidad' ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-700']"
                                >
                                    Especialidades
                                </button>
                            </div>

                            <!-- Lista de Servicios -->
                            <div v-if="tipoSeleccion === 'servicio'" class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                                <div
                                    v-for="servicio in servicios"
                                    :key="servicio.id"
                                    @click="seleccionarServicio(servicio)"
                                    class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition"
                                >
                                    <h4 class="font-semibold text-gray-800">{{ servicio.nombre }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ servicio.descripcion || 'Sin descripción' }}</p>
                                    <p class="text-indigo-600 font-bold mt-2">Bs. {{ Number(servicio.costo).toFixed(2) }}</p>
                                </div>
                            </div>

                            <!-- Lista de Especialidades -->
                            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4 max-h-96 overflow-y-auto">
                                <div
                                    v-for="especialidad in especialidades"
                                    :key="especialidad.id"
                                    @click="seleccionarEspecialidad(especialidad)"
                                    class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition"
                                >
                                    <h4 class="font-semibold text-gray-800">{{ especialidad.nombre }}</h4>
                                    <p class="text-sm text-gray-600 mt-1">{{ especialidad.descripcion || 'Sin descripción' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Paso 2: Seleccionar Médico -->
                    <div v-if="pasoActual === 2">
                        <h3 class="text-xl font-bold mb-4">Selecciona un Médico</h3>
                        
                        <div v-if="cargandoMedicos" class="text-center py-8">
                            <p class="text-gray-500">Cargando médicos...</p>
                        </div>
                        <div v-else-if="medicosDisponibles.length === 0" class="text-center py-8">
                            <p class="text-gray-500">No hay médicos disponibles.</p>
                        </div>
                        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div
                                v-for="medico in medicosDisponibles"
                                :key="medico.usuario_id"
                                @click="seleccionarMedico(medico)"
                                class="border border-gray-200 rounded-lg p-4 hover:border-indigo-500 cursor-pointer transition"
                            >
                                <h4 class="font-semibold text-gray-800">{{ medico.nombre_completo }}</h4>
                                <div v-if="medico.especialidades && medico.especialidades.length > 0" class="mt-2">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="esp in medico.especialidades"
                                            :key="esp"
                                            class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded"
                                        >
                                            {{ esp }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <SecondaryButton @click="volverAtras">← Atrás</SecondaryButton>
                        </div>
                    </div>

                    <!-- Paso 3: Seleccionar Fecha y Hora -->
                    <div v-if="pasoActual === 3">
                        <h3 class="text-xl font-bold mb-4">Selecciona Fecha y Hora</h3>
                        
                        <div class="mb-4">
                            <InputLabel for="fecha" value="Fecha *" />
                            <TextInput
                                id="fecha"
                                v-model="formulario.fecha"
                                type="date"
                                class="mt-1 block w-full"
                                :min="new Date().toISOString().split('T')[0]"
                                required
                            />
                            <InputError :message="formulario.errors.fecha" class="mt-2" />
                        </div>

                        <div v-if="formulario.fecha && horariosDisponibles.length > 0" class="mb-4">
                            <InputLabel value="Horarios Disponibles *" />
                            <div class="grid grid-cols-3 md:grid-cols-4 gap-2 mt-2">
                                <button
                                    v-for="hora in horariosDisponibles"
                                    :key="hora"
                                    @click="seleccionarHora(hora)"
                                    :class="['px-4 py-2 rounded-lg border transition', horaSeleccionada === hora ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-700 border-gray-300 hover:border-indigo-500']"
                                >
                                    {{ hora }}
                                </button>
                            </div>
                            <InputError :message="formulario.errors.hora" class="mt-2" />
                        </div>

                        <div v-if="cargandoHorarios" class="text-center py-4">
                            <p class="text-gray-500">Cargando horarios disponibles...</p>
                        </div>

                        <div class="mt-6 flex justify-between">
                            <SecondaryButton @click="volverAtras">← Atrás</SecondaryButton>
                        </div>
                    </div>

                    <!-- Paso 4: Confirmar -->
                    <div v-if="pasoActual === 4">
                        <h3 class="text-xl font-bold mb-4">Confirma tu Cita</h3>
                        
                        <div class="bg-gray-50 rounded-lg p-6 mb-4">
                            <div class="space-y-3">
                                <div>
                                    <span class="text-gray-500">Servicio:</span>
                                    <span class="ml-2 font-semibold">{{ servicioSeleccionado?.nombre || especialidadSeleccionada?.nombre }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Médico:</span>
                                    <span class="ml-2 font-semibold">{{ medicoSeleccionado?.nombre_completo }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Fecha:</span>
                                    <span class="ml-2 font-semibold">{{ formulario.fecha }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500">Hora:</span>
                                    <span class="ml-2 font-semibold">{{ formulario.hora }}</span>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <InputLabel for="motivo" value="Motivo de Consulta (Opcional)" />
                            <textarea
                                id="motivo"
                                v-model="formulario.motivo_consulta"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                                rows="3"
                                placeholder="Describe el motivo de tu consulta..."
                            ></textarea>
                            <InputError :message="formulario.errors.motivo_consulta" class="mt-2" />
                        </div>

                        <div class="flex justify-between">
                            <SecondaryButton @click="volverAtras">← Atrás</SecondaryButton>
                            <PrimaryButton @click="confirmarYGuardar" :disabled="formulario.processing">
                                Confirmar y Proceder al Pago
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

