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
    }, 60000);
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
                    <div v-if="!qrData" class="bg-gradient-to-r from-green-600 to-emerald-600 p-6">
                        <h3 class="text-2xl font-bold text-white">Resumen de la Cita</h3>
                    </div>
                    <div v-else class="bg-gradient-to-r from-indigo-600 to-blue-600 p-6 text-center">
                        <h3 class="text-2xl font-bold text-white">Escanea el QR para Pagar</h3>
                    </div>

                    <!-- Información de la Ficha -->
                    <div class="p-6">
                        <!-- Resumen y Selección (Solo visible si NO hay QR generado) -->
                        <div v-if="!qrData">
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
                                <h4 class="text-lg font-semibold mb-4">Método de Pago</h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <!-- QR -->
                                    <button
                                        @click="seleccionarMetodo('QR')"
                                        :class="['border-2 rounded-lg p-4 transition flex items-center justify-center gap-3', metodoSeleccionado === 'QR' ? 'border-indigo-500 bg-indigo-50' : 'border-gray-300 hover:border-indigo-500']"
                                    >
                                        <div class="text-4xl">📱</div>
                                        <p class="font-semibold text-lg">Pagar con QR</p>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Sección QR (Centrada y limpia) -->
                        <div v-if="qrData" class="flex flex-col items-center justify-center py-8">
                            <div class="bg-white p-4 rounded-xl shadow-lg border border-gray-100">
                                <img 
                                    :src="'data:image/png;base64,' + qrData.qrBase64" 
                                    alt="QR Code" 
                                    class="w-80 h-80 rounded-lg"
                                />
                            </div>
                            
                            <div class="text-center mt-8 space-y-4">
                                <div class="animate-pulse">
                                    <p class="text-lg font-medium text-indigo-600">Esperando pago...</p>
                                    <p class="text-sm text-gray-500">Escanea el código con tu aplicación bancaria</p>
                                </div>
                                
                                <div class="pt-4">
                                    <button
                                        @click="consultarEstado"
                                        :disabled="consultandoEstado"
                                        class="px-6 py-2 bg-white border border-gray-300 text-gray-700 rounded-full hover:bg-gray-50 transition text-sm font-medium shadow-sm"
                                    >
                                        {{ consultandoEstado ? 'Verificando...' : 'Verificar Estado Manualmente' }}
                                    </button>
                                </div>

                                <div class="pt-8">
                                    <Link
                                        :href="route('cliente.fichas.index')"
                                        class="text-gray-400 hover:text-gray-600 text-sm underline decoration-dotted"
                                    >
                                        Cancelar y volver
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <!-- Botones -->
                        <!-- Botones (Solo si no hay QR) -->
                        <div v-if="!qrData" class="flex justify-between">
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
