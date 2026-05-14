<?php

use App\Http\Controllers\Api\V1\InsumoController;
use App\Http\Controllers\Api\V1\ProductoController;
use App\Http\Controllers\Api\V1\CotizacionController;
use App\Http\Controllers\Api\V1\EventoController;
use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\NotificacionController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rutas API - Raspados Yeti ERP
|--------------------------------------------------------------------------
|
| Todas las rutas API están prefijadas automáticamente con /api
| por el RouteServiceProvider de Laravel.
|
| Estructura de versionado: /api/v1/...
|
*/

// ─── Ruta de autenticación (Sanctum - default) ──────────────────────────
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// ─── API v1 ─────────────────────────────────────────────────────────────
Route::prefix('v1')->group(function () {

    // ══════════════════════════════════════════════════════════════════════
    // ── Módulo A: Motor de Costeo ────────────────────────────────────────
    // ══════════════════════════════════════════════════════════════════════

    // ── Insumos (Materia Prima) ──────────────────────────────────────────
    // GET    /api/v1/insumos           → Listar todos (paginado)
    // POST   /api/v1/insumos           → Crear nuevo insumo
    // GET    /api/v1/insumos/{id}      → Ver detalle de un insumo
    // PUT    /api/v1/insumos/{id}      → Actualizar un insumo
    // DELETE /api/v1/insumos/{id}      → Eliminar un insumo
    Route::apiResource('insumos', InsumoController::class);

    // ── Productos (Snacks Finales) ───────────────────────────────────────
    // GET    /api/v1/productos           → Listar todos (paginado)
    // POST   /api/v1/productos           → Crear nuevo producto (con receta opcional)
    // GET    /api/v1/productos/{id}      → Ver detalle con receta
    // PUT    /api/v1/productos/{id}      → Actualizar producto y/o receta
    // DELETE /api/v1/productos/{id}      → Eliminar producto
    Route::apiResource('productos', ProductoController::class);

    // ── Motor de Costeo ──────────────────────────────────────────────────
    // GET  /api/v1/productos/{id}/costo-base → Calcular costo de producción unitario
    Route::get('productos/{id}/costo-base', [ProductoController::class, 'calcularCostoBase'])
         ->name('productos.costo-base');

    // POST /api/v1/cotizar-evento → Cotización completa de evento (insumos + transporte + nómina)
    Route::post('cotizar-evento', [ProductoController::class, 'cotizarEvento'])
         ->name('cotizar-evento');

        // POST /api/v1/cotizar-evento/pdf → Descargar cotización en PDF
        Route::post('cotizar-evento/pdf', [ProductoController::class, 'cotizarEventoPdf'])
            ->name('cotizar-evento.pdf');

    // POST /api/v1/cotizaciones → Crear cotizacion persistida
    Route::post('cotizaciones', [CotizacionController::class, 'store'])
        ->name('cotizaciones.store');

    // GET /api/v1/cotizaciones → Listado de cotizaciones
    Route::get('cotizaciones', [CotizacionController::class, 'index'])
        ->name('cotizaciones.index');

    // GET /api/v1/cotizaciones/{id}/pdf → Descargar PDF almacenado
    Route::get('cotizaciones/{id}/pdf', [CotizacionController::class, 'downloadStoredPdf'])
        ->name('cotizaciones.pdf');

    // POST /api/v1/cotizaciones/{id}/aprobar → Aprobar cotización y pasar a Agenda
    Route::post('cotizaciones/{id}/aprobar', [CotizacionController::class, 'aprobar'])
        ->name('cotizaciones.aprobar');

    // POST /api/v1/cotizaciones/{id}/cancelar → Cancelar cotización
    Route::post('cotizaciones/{id}/cancelar', [CotizacionController::class, 'cancelar'])
        ->name('cotizaciones.cancelar');

    // ══════════════════════════════════════════════════════════════════════
    // ── Módulo B: Calendario y Gestión de Eventos ────────────────────────
    // ══════════════════════════════════════════════════════════════════════

    // ── Eventos (CRUD) ───────────────────────────────────────────────────
    // GET    /api/v1/eventos              → Listar (con filtros: desde, hasta, estado, mes, anio)
    // POST   /api/v1/eventos              → Crear nuevo evento
    // GET    /api/v1/eventos/{id}         → Ver detalle de un evento
    // PUT    /api/v1/eventos/{id}         → Actualizar un evento
    // DELETE /api/v1/eventos/{id}         → Eliminar un evento
    Route::apiResource('eventos', EventoController::class);

    // ── Transición de Estado ─────────────────────────────────────────────
    // PATCH /api/v1/eventos/{id}/estado   → Cambiar estado (flujo validado)
    Route::patch('eventos/{id}/estado', [EventoController::class, 'cambiarEstado'])
         ->name('eventos.cambiar-estado');
    // ──────────────── Módulo C: CRM (Clientes y Eventos) ──────────────────────
    Route::apiResource('clientes', ClienteController::class);

    // ── Notificaciones del sistema ──────────────────────────────────────
    Route::get('notificaciones', [NotificacionController::class, 'index'])->name('notificaciones.index');
    Route::patch('notificaciones/{id}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::patch('notificaciones/leer-todas', [NotificacionController::class, 'marcarTodas'])->name('notificaciones.leer-todas');
});
