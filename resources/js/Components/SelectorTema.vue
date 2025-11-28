<template>
    <div class="selector-tema tema-fondo-secundario shadow-md p-4 rounded-lg mb-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Selector de Tema -->
            <div>
                <label class="block text-sm font-medium mb-2 tema-texto">
                    Tema:
                </label>
                <select 
                    v-model="temaSeleccionado" 
                    @change="aplicarTema"
                    class="input-tema w-full"
                >
                    <option value="ninos">Niños</option>
                    <option value="jovenes">Jóvenes</option>
                    <option value="adultos">Adultos</option>
                </select>
            </div>

            <!-- Selector de Modo -->
            <div>
                <label class="block text-sm font-medium mb-2 tema-texto">
                    Modo:
                </label>
                <select 
                    v-model="modoSeleccionado" 
                    @change="aplicarTema"
                    class="input-tema w-full"
                >
                    <option value="dia">Día</option>
                    <option value="noche">Noche</option>
                    <option value="auto">Automático (según horario)</option>
                </select>
            </div>

            <!-- Selector de Tamaño de Fuente -->
            <div>
                <label class="block text-sm font-medium mb-2 tema-texto">
                    Tamaño de Letra:
                </label>
                <select 
                    v-model="tamañoFuente" 
                    @change="aplicarAccesibilidad"
                    class="input-tema w-full"
                >
                    <option value="pequeño">Pequeño</option>
                    <option value="normal">Normal</option>
                    <option value="grande">Grande</option>
                    <option value="muy-grande">Muy Grande</option>
                </select>
            </div>

            <!-- Selector de Contraste -->
            <div>
                <label class="block text-sm font-medium mb-2 tema-texto">
                    Contraste:
                </label>
                <select 
                    v-model="contraste" 
                    @change="aplicarAccesibilidad"
                    class="input-tema w-full"
                >
                    <option value="normal">Normal</option>
                    <option value="alto">Alto</option>
                </select>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';

const pagina = usePage();
const preferenciasIniciales = pagina.props.preferenciasTema || {
    tema: 'adultos',
    modo: 'dia',
    tamaño_fuente: 'normal',
    contraste: 'normal',
    modo_auto: false,
};

const temaSeleccionado = ref(preferenciasIniciales.tema);
const modoSeleccionado = ref(preferenciasIniciales.modo);
const tamañoFuente = ref(preferenciasIniciales.tamaño_fuente);
const contraste = ref(preferenciasIniciales.contraste);
const modoAuto = ref(preferenciasIniciales.modo_auto || false);

function aplicarTema(guardarEnBD = true) {
    const html = document.documentElement;
    const body = document.body;
    
    html.setAttribute('data-tema', temaSeleccionado.value);
    body.setAttribute('data-tema', temaSeleccionado.value);
    
    if (modoSeleccionado.value === 'auto' || modoAuto.value) {
        detectarHorario();
    } else {
        html.setAttribute('data-modo', modoSeleccionado.value);
        body.setAttribute('data-modo', modoSeleccionado.value);
    }
    
    // Guardar en BD solo si se solicita (cuando el usuario cambia manualmente)
    if (guardarEnBD) {
        guardarPreferencias();
    }
}

function detectarHorario() {
    const hora = new Date().getHours();
    const modo = (hora >= 6 && hora < 20) ? 'dia' : 'noche';
    const html = document.documentElement;
    const body = document.body;
    html.setAttribute('data-modo', modo);
    body.setAttribute('data-modo', modo);
}

function aplicarAccesibilidad(guardarEnBD = true) {
    const html = document.documentElement;
    const body = document.body;
    
    html.setAttribute('data-accesibilidad', tamañoFuente.value);
    body.setAttribute('data-accesibilidad', tamañoFuente.value);
    html.setAttribute('data-contraste', contraste.value);
    body.setAttribute('data-contraste', contraste.value);
    
    // Guardar en BD solo si se solicita (cuando el usuario cambia manualmente)
    if (guardarEnBD) {
        guardarPreferencias();
    }
}

function guardarPreferencias() {
    // Solo guardar si el usuario está autenticado
    if (!pagina.props.auth?.user) {
        // Si no hay usuario, guardar solo en localStorage
        localStorage.setItem('tema', temaSeleccionado.value);
        localStorage.setItem('modo', modoSeleccionado.value);
        localStorage.setItem('tamañoFuente', tamañoFuente.value);
        localStorage.setItem('contraste', contraste.value);
        return;
    }

    // Guardar en BD mediante petición POST con Inertia
    router.post(route('tema.guardar'), {
        tema: temaSeleccionado.value,
        modo: modoSeleccionado.value,
        tamaño_fuente: tamañoFuente.value,
        contraste: contraste.value,
        modo_auto: modoAuto.value,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ['preferenciasTema'],
        onSuccess: () => {
            // Actualizar preferencias locales después de guardar
            const nuevaPreferencia = pagina.props.preferenciasTema;
            if (nuevaPreferencia) {
                temaSeleccionado.value = nuevaPreferencia.tema;
                modoSeleccionado.value = nuevaPreferencia.modo;
                tamañoFuente.value = nuevaPreferencia.tamaño_fuente;
                contraste.value = nuevaPreferencia.contraste;
            }
        },
    });
}

// Watch para detectar cambios en modo_auto
watch(modoSeleccionado, (nuevoValor) => {
    modoAuto.value = nuevoValor === 'auto';
    if (nuevoValor === 'auto') {
        setInterval(detectarHorario, 3600000); // Cada hora
    }
});

onMounted(() => {
    // Aplicar preferencias iniciales sin guardar en BD (solo aplicar estilos)
    aplicarTema(false); // false = no guardar en BD
    aplicarAccesibilidad(false); // false = no guardar en BD
    
    // Si está en modo automático, verificar cada hora
    if (modoSeleccionado.value === 'auto' || modoAuto.value) {
        setInterval(detectarHorario, 3600000); // Cada hora
    }
});
</script>

<style scoped>
.selector-tema {
    background-color: var(--color-fondo-secundario);
    border: 1px solid var(--color-borde);
}
</style>

