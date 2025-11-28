<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\SalaController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\RolController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\TemaController;
use App\Http\Controllers\MetodoPagoController;
use App\Http\Controllers\PagoController;
use App\Http\Controllers\PlanPagoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\EspecialidadController;
use App\Http\Controllers\FichaController;
use App\Http\Controllers\SeguimientoController;
use App\Http\Controllers\HistorialClinicoController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\BusquedaController;
use App\Http\Controllers\ContactoController;
use App\Http\Controllers\ServicioClienteController;
use App\Http\Controllers\FichaClienteController;
use App\Http\Controllers\PagoClienteController;

Route::get('/', function () {
    // Si el usuario está autenticado, redirigir al dashboard
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    
    // Si no está autenticado, mostrar la página de bienvenida
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

// Ruta para formulario de contacto (pública)
Route::post('/contacto/enviar', [ContactoController::class, 'enviarMensaje'])->name('contacto.enviar');

// Callback de PagoFácil (pública, sin autenticación)
Route::post('/cliente/pagos/callback', [PagoClienteController::class, 'callback'])->name('cliente.pagos.callback');

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Rutas de Roles
    Route::get('/roles', [RolController::class, 'paginaPrincipalRol'])->name('roles.index');
    Route::get('/roles/crear', [RolController::class, 'crearRol'])->name('roles.create');
    Route::post('/roles', [RolController::class, 'guardarRol'])->name('roles.store');
    Route::get('/roles/{id}', [RolController::class, 'mostrarRol'])->name('roles.show');
    Route::get('/roles/{id}/editar', [RolController::class, 'editarRol'])->name('roles.edit');
    Route::put('/roles/{id}', [RolController::class, 'actualizarRol'])->name('roles.update');
    Route::delete('/roles/{id}', [RolController::class, 'eliminarRol'])->name('roles.destroy');

    // Rutas de Permisos
    Route::get('/permisos', [PermisoController::class, 'paginaPrincipalPermiso'])->name('permisos.index');
    Route::get('/permisos/crear', [PermisoController::class, 'crearPermiso'])->name('permisos.create');
    Route::post('/permisos', [PermisoController::class, 'guardarPermiso'])->name('permisos.store');
    Route::get('/permisos/{id}', [PermisoController::class, 'mostrarPermiso'])->name('permisos.show');
    Route::get('/permisos/{id}/editar', [PermisoController::class, 'editarPermiso'])->name('permisos.edit');
    Route::put('/permisos/{id}', [PermisoController::class, 'actualizarPermiso'])->name('permisos.update');
    Route::delete('/permisos/{id}', [PermisoController::class, 'eliminarPermiso'])->name('permisos.destroy');

    // Rutas de Salas
    Route::get('/salas', [SalaController::class, 'paginaPrincipalSala'])->name('salas.index');
    Route::get('/salas/crear', [SalaController::class, 'crearSala'])->name('salas.create');
    Route::post('/salas', [SalaController::class, 'guardarSala'])->name('salas.store');
    Route::get('/salas/{id}', [SalaController::class, 'mostrarSala'])->name('salas.show');
    Route::get('/salas/{id}/editar', [SalaController::class, 'editarSala'])->name('salas.edit');
    Route::put('/salas/{id}', [SalaController::class, 'actualizarSala'])->name('salas.update');
    Route::delete('/salas/{id}', [SalaController::class, 'eliminarSala'])->name('salas.destroy');

    // Rutas de Usuarios
    Route::get('/usuarios', [UsuarioController::class, 'paginaPrincipalUsuario'])->name('usuarios.index');
    Route::get('/usuarios/crear', [UsuarioController::class, 'crearUsuario'])->name('usuarios.create');
    Route::post('/usuarios', [UsuarioController::class, 'guardarUsuario'])->name('usuarios.store');
    Route::get('/usuarios/{id}', [UsuarioController::class, 'mostrarUsuario'])->name('usuarios.show');
    Route::get('/usuarios/{id}/editar', [UsuarioController::class, 'editarUsuario'])->name('usuarios.edit');
    Route::put('/usuarios/{id}', [UsuarioController::class, 'actualizarUsuario'])->name('usuarios.update');
    Route::delete('/usuarios/{id}', [UsuarioController::class, 'eliminarUsuario'])->name('usuarios.destroy');

    // Rutas de Menú
    Route::get('/menu', [MenuController::class, 'paginaPrincipalMenu'])->name('menu.index');
    Route::post('/menu', [MenuController::class, 'guardarItem'])->name('menu.store');
    Route::put('/menu/{id}', [MenuController::class, 'actualizarItem'])->name('menu.update');
    Route::delete('/menu/{id}', [MenuController::class, 'eliminarItem'])->name('menu.destroy');
    Route::get('/api/menu', [MenuController::class, 'obtenerMenu'])->name('menu.api');

    // Rutas de Tema
    Route::post('/api/tema/preferencias', [TemaController::class, 'guardarPreferencias'])->name('tema.guardar');
    Route::get('/api/tema/preferencias', [TemaController::class, 'obtenerPreferencias'])->name('tema.obtener');

    // Rutas de Métodos de Pago
    Route::get('/metodos-pago', [MetodoPagoController::class, 'paginaPrincipalMetodoPago'])->name('metodos-pago.index');
    Route::post('/metodos-pago', [MetodoPagoController::class, 'guardarMetodoPago'])->name('metodos-pago.store');
    Route::put('/metodos-pago/{id}', [MetodoPagoController::class, 'actualizarMetodoPago'])->name('metodos-pago.update');
    Route::delete('/metodos-pago/{id}', [MetodoPagoController::class, 'eliminarMetodoPago'])->name('metodos-pago.destroy');

    // Rutas de Pagos
    Route::get('/pagos', [PagoController::class, 'paginaPrincipalPago'])->name('pagos.index');
    Route::post('/pagos/unico', [PagoController::class, 'guardarPagoUnico'])->name('pagos.guardar-unico');
    Route::post('/pagos/cuota', [PagoController::class, 'guardarPagoCuota'])->name('pagos.guardar-cuota');
    Route::get('/pagos/{id}', [PagoController::class, 'mostrarPago'])->name('pagos.show');

    // Rutas de Planes de Pago
    Route::get('/planes-pago', [PlanPagoController::class, 'paginaPrincipalPlanPago'])->name('planes-pago.index');
    Route::post('/planes-pago', [PlanPagoController::class, 'crearPlanPago'])->name('planes-pago.store');
    Route::get('/planes-pago/{id}', [PlanPagoController::class, 'mostrarPlanPago'])->name('planes-pago.show');
    Route::put('/planes-pago/{id}/estado', [PlanPagoController::class, 'actualizarEstadoPlanPago'])->name('planes-pago.actualizar-estado');

    // Rutas de Servicios
    Route::get('/servicios', [ServicioController::class, 'paginaPrincipalServicio'])->name('servicios.index');
    Route::get('/servicios/crear', [ServicioController::class, 'crearServicio'])->name('servicios.create');
    Route::post('/servicios', [ServicioController::class, 'guardarServicio'])->name('servicios.store');
    Route::get('/servicios/{id}', [ServicioController::class, 'mostrarServicio'])->name('servicios.show');
    Route::get('/servicios/{id}/editar', [ServicioController::class, 'editarServicio'])->name('servicios.edit');
    Route::put('/servicios/{id}', [ServicioController::class, 'actualizarServicio'])->name('servicios.update');
    Route::delete('/servicios/{id}', [ServicioController::class, 'eliminarServicio'])->name('servicios.destroy');

    // Rutas de Especialidades
    Route::get('/especialidades', [EspecialidadController::class, 'paginaPrincipalEspecialidad'])->name('especialidades.index');
    Route::get('/especialidades/crear', [EspecialidadController::class, 'crearEspecialidad'])->name('especialidades.create');
    Route::post('/especialidades', [EspecialidadController::class, 'guardarEspecialidad'])->name('especialidades.store');
    Route::get('/especialidades/{id}', [EspecialidadController::class, 'mostrarEspecialidad'])->name('especialidades.show');
    Route::get('/especialidades/{id}/editar', [EspecialidadController::class, 'editarEspecialidad'])->name('especialidades.edit');
    Route::put('/especialidades/{id}', [EspecialidadController::class, 'actualizarEspecialidad'])->name('especialidades.update');
    Route::delete('/especialidades/{id}', [EspecialidadController::class, 'eliminarEspecialidad'])->name('especialidades.destroy');

    // Rutas de Fichas
    Route::get('/fichas', [FichaController::class, 'paginaPrincipalFicha'])->name('fichas.index');
    Route::get('/fichas/crear', [FichaController::class, 'crearFicha'])->name('fichas.create');
    Route::post('/fichas', [FichaController::class, 'guardarFicha'])->name('fichas.store');
    Route::get('/fichas/horarios/disponibles', [FichaController::class, 'horariosDisponibles'])->name('fichas.horarios-disponibles');
    Route::get('/fichas/{id}', [FichaController::class, 'mostrarFicha'])->name('fichas.show');
    Route::get('/fichas/{id}/editar', [FichaController::class, 'editarFicha'])->name('fichas.edit');
    Route::put('/fichas/{id}', [FichaController::class, 'actualizarFicha'])->name('fichas.update');
    Route::delete('/fichas/{id}', [FichaController::class, 'eliminarFicha'])->name('fichas.destroy');

    // Rutas de Seguimientos
    Route::get('/seguimientos', [SeguimientoController::class, 'paginaPrincipalSeguimiento'])->name('seguimientos.index');
    Route::get('/seguimientos/crear', [SeguimientoController::class, 'crearSeguimiento'])->name('seguimientos.create');
    Route::post('/seguimientos', [SeguimientoController::class, 'guardarSeguimiento'])->name('seguimientos.store');
    Route::get('/seguimientos/{id}', [SeguimientoController::class, 'mostrarSeguimiento'])->name('seguimientos.show');
    Route::get('/seguimientos/{id}/editar', [SeguimientoController::class, 'editarSeguimiento'])->name('seguimientos.edit');
    Route::put('/seguimientos/{id}', [SeguimientoController::class, 'actualizarSeguimiento'])->name('seguimientos.update');
    Route::delete('/seguimientos/{id}', [SeguimientoController::class, 'eliminarSeguimiento'])->name('seguimientos.destroy');

    // Rutas de Historiales Clínicos
    Route::get('/historiales-clinicos', [HistorialClinicoController::class, 'paginaPrincipalHistorialClinico'])->name('historiales-clinicos.index');
    Route::get('/historiales-clinicos/crear', [HistorialClinicoController::class, 'crearHistorialClinico'])->name('historiales-clinicos.create');
    Route::post('/historiales-clinicos', [HistorialClinicoController::class, 'guardarHistorialClinico'])->name('historiales-clinicos.store');
    Route::get('/historiales-clinicos/{id}', [HistorialClinicoController::class, 'mostrarHistorialClinico'])->name('historiales-clinicos.show');
    Route::get('/historiales-clinicos/{id}/editar', [HistorialClinicoController::class, 'editarHistorialClinico'])->name('historiales-clinicos.edit');
    Route::put('/historiales-clinicos/{id}', [HistorialClinicoController::class, 'actualizarHistorialClinico'])->name('historiales-clinicos.update');
    Route::delete('/historiales-clinicos/{id}', [HistorialClinicoController::class, 'eliminarHistorialClinico'])->name('historiales-clinicos.destroy');

    // Rutas de Reportes
    Route::get('/reportes', [ReporteController::class, 'paginaPrincipalReporte'])->name('reportes.index');
    Route::get('/reportes/crear', [ReporteController::class, 'crearReporte'])->name('reportes.create');
    Route::post('/reportes', [ReporteController::class, 'guardarReporte'])->name('reportes.store');
    Route::get('/reportes/{id}', [ReporteController::class, 'mostrarReporte'])->name('reportes.show');
    Route::get('/reportes/{id}/editar', [ReporteController::class, 'editarReporte'])->name('reportes.edit');
    Route::put('/reportes/{id}', [ReporteController::class, 'actualizarReporte'])->name('reportes.update');
    Route::delete('/reportes/{id}', [ReporteController::class, 'eliminarReporte'])->name('reportes.destroy');

    // Ruta de Búsqueda
    Route::get('/buscar', [BusquedaController::class, 'buscar'])->name('buscar');

    // Rutas para Cliente
    Route::prefix('cliente')->name('cliente.')->group(function () {
        // Servicios para Cliente
        Route::get('/servicios', [ServicioClienteController::class, 'listarServicios'])->name('servicios.index');
        Route::get('/servicios/{id}', [ServicioClienteController::class, 'mostrarServicio'])->name('servicios.show');
        
        // Fichas para Cliente
        Route::get('/fichas', [FichaClienteController::class, 'paginaPrincipal'])->name('fichas.index');
        Route::get('/fichas/crear', [FichaClienteController::class, 'crearFicha'])->name('fichas.create');
        Route::post('/fichas', [FichaClienteController::class, 'guardarFicha'])->name('fichas.store');
        Route::get('/fichas/medicos', [FichaClienteController::class, 'obtenerMedicos'])->name('fichas.medicos');
        Route::get('/fichas/horarios', [FichaClienteController::class, 'obtenerHorariosDisponibles'])->name('fichas.horarios');
        
        // Pagos para Cliente
        Route::get('/pagos/procesar/{fichaId}', [PagoClienteController::class, 'procesarPago'])->name('pagos.procesar');
        Route::post('/pagos/generar-qr', [PagoClienteController::class, 'generarQr'])->name('pagos.generar-qr');
        Route::post('/pagos/procesar-tarjeta', [PagoClienteController::class, 'procesarTarjeta'])->name('pagos.procesar-tarjeta');
        Route::get('/pagos/consultar-estado', [PagoClienteController::class, 'consultarEstado'])->name('pagos.consultar-estado');
    });
});
