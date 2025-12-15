<script setup>
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

const props = defineProps({
    ficha: Object,
    saldoPendiente: Number,
    totalPagado: Number,
    pagoPendiente: Object,
});

const pagina = usePage();
const metodoSeleccionado = ref(null); // 'QR' o 'TARJETA'
const qrData = ref(null);
const consultandoEstado = ref(false);
const intervaloConsulta = ref(null);

const formularioTarjeta = useForm({
    ficha_id: props.ficha.id,
    numero_tarjeta: '',
    nombre_titular: '',
    fecha_vencimiento: '',
    cvv: '',
});

function formatearMoneda(valor) {
    if (!valor) return 'Bs 0.00';
    return new Intl.NumberFormat('es-BO', { 
        style: 'currency', 
        currency: 'BOB' 
    }).format(Number(valor));
}

function formatearFecha(fecha) {
    if (!fecha) return '';
    return new Date(fecha).toLocaleDateString('es-BO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}

function formatearHora(hora) {
    if (!hora) return '';
    if (typeof hora === 'string' && hora.includes('T')) {
        const [, timePart] = hora.split('T');
        if (timePart) return timePart.slice(0, 5);
    }
    return hora;
}

function seleccionarMetodo(metodo) {
    metodoSeleccionado.value = metodo;
    if (metodo === 'QR') {
        generarQr();
    }
}

function generarQr() {
    console.log('🔄 [Procesar] Iniciando generación de QR...');
    router.post(route('cliente.pagos.generar-qr'), {
        ficha_id: props.ficha.id,
    }, {
        preserveScroll: true,
        onSuccess: (page) => {
            console.log('✅ [Procesar] Respuesta exitosa', {
                flash: page.props.flash,
                qr_data: page.props.flash?.qr_data,
            });
            
            // Intentar obtener qr_data de diferentes ubicaciones
            const qrDataRecibido = page.props.flash?.qr_data || page.props.qr_data || null;
            
            if (qrDataRecibido) {
                console.log('📱 [Procesar] Datos QR recibidos:', qrDataRecibido);
                qrData.value = qrDataRecibido;
                metodoSeleccionado.value = 'QR';
                // Iniciar consulta automática cada 5 segundos
                iniciarConsultaAutomatica();
            } else {
                console.warn('⚠️ [Procesar] No se recibieron datos QR. Props completos:', page.props);
            }
        },
        onError: (errors) => {
            console.error('❌ [Procesar] Error al generar QR:', errors);
        },
    });
}

function iniciarConsultaAutomatica() {
    // Limpiar intervalo anterior si existe
    if (intervaloConsulta.value) {
        clearInterval(intervaloConsulta.value);
    }

    // Consultar estado cada 5 segundos
    console.log('🔄 [Procesar] Iniciando consulta automática cada 5 segundos');
    intervaloConsulta.value = setInterval(() => {
        consultarEstado();
    }, 30000);
}

function consultarEstado() {
    if (consultandoEstado.value) {
        console.log('⏸️ [Procesar] Consulta en progreso, omitiendo...');
        return;
    }

    // Usar transactionId directamente si está disponible (como en Postman)
    const transactionId = qrData.value?.transactionId;
    
    if (!transactionId) {
        console.warn('⚠️ [Procesar] No hay transactionId disponible');
        return;
    }

    console.log('🔄 [Procesar] Iniciando consulta de estado...', {
        transactionId: transactionId,
        timestamp: new Date().toISOString()
    });

    consultandoEstado.value = true;
    axios.get(route('cliente.pagos.consultar-estado'), {
        params: { transaction_id: transactionId }
    })
        .then(response => {
            console.log('📥 [Procesar] Respuesta completa recibida:', {
                status: response.status,
                statusText: response.statusText,
                data: response.data,
                dataType: typeof response.data,
                dataKeys: Object.keys(response.data || {}),
            });

            console.log('🔍 [Procesar] Analizando respuesta:', {
                'response.data': response.data,
                'response.data.status': response.data?.status,
                'response.data.success': response.data?.success,
                'response.data.message': response.data?.message,
            });

            const status = response.data?.status;
            const success = response.data?.success;

            console.log('💳 [Procesar] Estado del pago:', {
                status: status,
                success: success,
                esPAID: status === 'PAID',
                esPENDING: status === 'PENDING',
                esCANCELLED: status === 'CANCELLED',
                esEXPIRED: status === 'EXPIRED',
            });

            if (status === 'PAID') {
                console.log('✅ [Procesar] ¡PAGO CONFIRMADO! Deteniendo consultas y redirigiendo...');
                clearInterval(intervaloConsulta.value);
                intervaloConsulta.value = null;
                router.visit(route('cliente.fichas.index'), {
                    onSuccess: () => {
                        console.log('✅ [Procesar] Redirección exitosa');
                        alert('✅ Pago confirmado exitosamente');
                    },
                    onError: (errors) => {
                        console.error('❌ [Procesar] Error en redirección:', errors);
                    }
                });
            } else if (status === 'PENDING') {
                console.log('⏳ [Procesar] Pago aún pendiente, continuando consultas...');
            } else if (status === 'CANCELLED') {
                console.log('❌ [Procesar] Pago cancelado, deteniendo consultas...');
                clearInterval(intervaloConsulta.value);
                intervaloConsulta.value = null;
            } else if (status === 'EXPIRED') {
                console.log('⏰ [Procesar] QR expirado, deteniendo consultas...');
                clearInterval(intervaloConsulta.value);
                intervaloConsulta.value = null;
            } else {
                console.warn('⚠️ [Procesar] Estado desconocido:', status);
            }
        })
        .catch(error => {
            console.error('❌ [Procesar] Error al consultar estado:', {
                message: error.message,
                response: error.response,
                responseData: error.response?.data,
                responseStatus: error.response?.status,
                responseStatusText: error.response?.statusText,
                stack: error.stack,
            });
        })
        .finally(() => {
            consultandoEstado.value = false;
            console.log('🏁 [Procesar] Consulta finalizada');
        });
}

function procesarTarjeta() {
    // Limpiar espacios del número de tarjeta antes de enviar
    const datos = {
        ...formularioTarjeta.data(),
        numero_tarjeta: formularioTarjeta.numero_tarjeta.replace(/\s/g, ''),
    };

    formularioTarjeta.transform(() => datos).post(route('cliente.pagos.procesar-tarjeta'), {
        preserveScroll: true,
        onSuccess: () => {
            router.visit(route('cliente.fichas.index'));
        },
        onError: (errors) => {
            console.error('Error al procesar tarjeta:', errors);
        },
    });
}

function formatearNumeroTarjeta(event) {
    let valor = event.target.value.replace(/\s/g, '');
    valor = valor.replace(/(.{4})/g, '$1 ').trim();
    formularioTarjeta.numero_tarjeta = valor;
}

function formatearFechaVencimiento(event) {
    let valor = event.target.value.replace(/\D/g, '');
    if (valor.length >= 2) {
        valor = valor.slice(0, 2) + '/' + valor.slice(2, 4);
    }
    formularioTarjeta.fecha_vencimiento = valor;
}

// Limpiar intervalo al desmontar
onMounted(() => {
    // Si hay QR pendiente, cargarlo
    if (props.pagoPendiente?.qr_base64) {
        qrData.value = {
            qrBase64: props.pagoPendiente.qr_base64,
            transactionId: props.pagoPendiente.pagofacil_transaction_id,
        };
        metodoSeleccionado.value = 'QR';
        iniciarConsultaAutomatica();
    }

    // Si hay qr_data en flash, usarlo
    const qrDataFlash = pagina.props.flash?.qr_data || pagina.props.qr_data;
    if (qrDataFlash) {
        console.log('📱 [Procesar] Cargando QR desde flash:', qrDataFlash);
        qrData.value = qrDataFlash;
        metodoSeleccionado.value = 'QR';
        iniciarConsultaAutomatica();
    }
});

// Limpiar al desmontar
onUnmounted(() => {
    if (intervaloConsulta.value) {
        clearInterval(intervaloConsulta.value);
    }
});
</script>

<template>
    <AppLayout title="Procesar Pago">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Procesar Pago
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Header -->
                    <div class="bg-gradient-to-r from-green-600 to-emerald-600 p-6">
                        <h3 class="text-2xl font-bold text-white">Resumen de la Cita</h3>
                    </div>

                    <!-- Información de la Ficha -->
                    <div class="p-6">
                        <div class="bg-gray-50 rounded-lg p-6 mb-6">
                            <div class="space-y-4">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Servicio:</span>
                                    <span class="font-semibold">{{ ficha.servicio?.nombre }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Médico:</span>
                                    <span class="font-semibold">
                                        {{ ficha.medico?.usuario?.persona?.nombre_completo }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Fecha:</span>
                                    <span class="font-semibold">{{ formatearFecha(ficha.fecha) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Hora:</span>
                                    <span class="font-semibold">{{ formatearHora(ficha.hora) }}</span>
                                </div>
                                <div v-if="ficha.motivo_consulta" class="pt-4 border-t">
                                    <span class="text-gray-600">Motivo:</span>
                                    <p class="mt-1 text-gray-800">{{ ficha.motivo_consulta }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Total a Pagar -->
                        <div class="bg-indigo-50 rounded-lg p-6 mb-6">
                            <div class="flex justify-between items-center">
                                <span class="text-xl font-semibold text-gray-800">Total a Pagar:</span>
                                <span class="text-3xl font-bold text-indigo-600">
                                    {{ formatearMoneda(saldoPendiente) }}
                                </span>
                            </div>
                            <div v-if="totalPagado > 0" class="mt-2 text-sm text-gray-600">
                                Ya pagado: {{ formatearMoneda(totalPagado) }}
                            </div>
                        </div>

                        <!-- Métodos de Pago -->
                        <div class="mb-6">
                            <h4 class="text-lg font-semibold mb-4">Selecciona Método de Pago</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <!-- QR -->
                                <button
                                    @click="seleccionarMetodo('QR')"
                                    :class="['border-2 rounded-lg p-4 transition', metodoSeleccionado === 'QR' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-500']"
                                >
                                    <div class="text-4xl mb-2">📱</div>
                                    <p class="font-semibold">Pago con QR</p>
                                </button>

                                <!-- Tarjeta -->
                                <button
                                    @click="seleccionarMetodo('TARJETA')"
                                    :class="['border-2 rounded-lg p-4 transition', metodoSeleccionado === 'TARJETA' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-500']"
                                >
                                    <div class="text-4xl mb-2">💳</div>
                                    <p class="font-semibold">Tarjeta</p>
                                </button>
                            </div>
                        </div>

                        <!-- Sección QR -->
                        <div v-if="metodoSeleccionado === 'QR' && qrData" class="mb-6 bg-blue-50 rounded-lg p-6">
                            <h5 class="font-semibold text-gray-800 mb-4 text-center">Escanea el QR con tu app de pagos</h5>
                            <div class="flex justify-center mb-4">
                                <img 
                                    :src="'data:image/png;base64,' + qrData.qrBase64" 
                                    alt="QR Code" 
                                    class="w-64 h-64 border-2 border-gray-300 rounded-lg"
                                />
                            </div>
                            <div class="text-center">
                                <p class="text-sm text-gray-600 mb-2">Esperando confirmación de pago...</p>
                                <button
                                    @click="consultarEstado"
                                    :disabled="consultandoEstado"
                                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 disabled:opacity-50"
                                >
                                    {{ consultandoEstado ? 'Consultando...' : 'Consultar Estado' }}
                                </button>
                            </div>
                        </div>

                        <!-- Sección Tarjeta -->
                        <div v-if="metodoSeleccionado === 'TARJETA'" class="mb-6 bg-gray-50 rounded-lg p-6">
                            <h5 class="font-semibold text-gray-800 mb-4">Datos de la Tarjeta</h5>
                            <form @submit.prevent="procesarTarjeta" class="space-y-4">
                                <div>
                                    <InputLabel for="numero_tarjeta" value="Número de Tarjeta *" />
                                    <TextInput
                                        id="numero_tarjeta"
                                        v-model="formularioTarjeta.numero_tarjeta"
                                        type="text"
                                        class="mt-1 block w-full"
                                        @input="formatearNumeroTarjeta"
                                        placeholder="1234 5678 9012 3456"
                                        maxlength="19"
                                        required
                                    />
                                    <InputError :message="formularioTarjeta.errors.numero_tarjeta" class="mt-2" />
                                </div>

                                <div>
                                    <InputLabel for="nombre_titular" value="Nombre del Titular *" />
                                    <TextInput
                                        id="nombre_titular"
                                        v-model="formularioTarjeta.nombre_titular"
                                        type="text"
                                        class="mt-1 block w-full"
                                        placeholder="JUAN PEREZ"
                                        required
                                    />
                                    <InputError :message="formularioTarjeta.errors.nombre_titular" class="mt-2" />
                                </div>

                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel for="fecha_vencimiento" value="Fecha Vencimiento (MM/AA) *" />
                                        <TextInput
                                            id="fecha_vencimiento"
                                            v-model="formularioTarjeta.fecha_vencimiento"
                                            type="text"
                                            class="mt-1 block w-full"
                                            @input="formatearFechaVencimiento"
                                            placeholder="12/25"
                                            maxlength="5"
                                            required
                                        />
                                        <InputError :message="formularioTarjeta.errors.fecha_vencimiento" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel for="cvv" value="CVV *" />
                                        <TextInput
                                            id="cvv"
                                            v-model="formularioTarjeta.cvv"
                                            type="text"
                                            class="mt-1 block w-full"
                                            placeholder="123"
                                            maxlength="4"
                                            required
                                        />
                                        <InputError :message="formularioTarjeta.errors.cvv" class="mt-2" />
                                    </div>
                                </div>

                                <div class="mt-6">
                                    <PrimaryButton
                                        type="submit"
                                        :disabled="formularioTarjeta.processing"
                                        class="w-full"
                                    >
                                        {{ formularioTarjeta.processing ? 'Procesando...' : 'Procesar Pago' }}
                                    </PrimaryButton>
                                </div>
                            </form>
                        </div>

                        <!-- Botones -->
                        <div class="flex justify-between">
                            <Link
                                :href="route('cliente.fichas.index')"
                                class="px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold rounded-lg transition"
                            >
                                Cancelar
                            </Link>
                            <div v-if="!metodoSeleccionado" class="text-sm text-gray-500 flex items-center">
                                Selecciona un método de pago
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
