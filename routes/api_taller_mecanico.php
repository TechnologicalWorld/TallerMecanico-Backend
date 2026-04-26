<?php

use App\Http\Controllers\Api\V1\ClienteController;
use App\Http\Controllers\Api\V1\VehiculoController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum', 'branch.active'])->prefix('v1')->group(function () {
    
    // Rutas para Clientes
    Route::prefix('clientes')->name('clientes.')->group(function () {
        // Listado de clientes
        Route::get('/', [ClienteController::class, 'index'])
            ->middleware('can:clientes.ver')
            ->name('index');
        // Registro de cliente
        Route::post('/', [ClienteController::class, 'store'])
            ->middleware('can:clientes.crear')
            ->name('store');
        // Ver detalle de un cliente    
        Route::get('/{cliente}', [ClienteController::class, 'show'])
            ->middleware('can:clientes.ver')
            ->name('show');
        // Actualización de cliente
        Route::put('/{cliente}', [ClienteController::class, 'update'])
            ->middleware('can:clientes.editar')
            ->name('update');
        // Borrado logico de un cliente
        Route::delete('/{cliente}', [ClienteController::class, 'destroy'])
            ->middleware('can:clientes.eliminar')
            ->name('destroy');
        // Cambio de estado rápido (Activo/Inactivo)
        Route::patch('/{cliente}/toggle-status', [ClienteController::class, 'toggleStatus'])
            ->middleware('can:clientes.editar')
            ->name('toggle-status');
        // Restaurar cliente eliminado
        Route::post('/{cliente}/restore', [ClienteController::class, 'restore'])
            ->middleware('can:clientes.eliminar')
            ->name('restore');
    });

    // Rutas para Vehículos
    Route::prefix('vehiculos')->name('vehiculos.')->group(function () {
        // Listado de vehículos
        Route::get('/', [VehiculoController::class, 'index'])
            ->middleware('can:vehiculos.ver')
            ->name('index');

        // Registro de vehículo
        Route::post('/', [VehiculoController::class, 'store'])
            ->middleware('can:vehiculos.crear')
            ->name('store');

        // Detalle de vehículo
        Route::get('/{vehiculo}', [VehiculoController::class, 'show'])
            ->middleware('can:vehiculos.ver')
            ->name('show');

        // Actualización técnica
        Route::put('/{vehiculo}', [VehiculoController::class, 'update'])
            ->middleware('can:vehiculos.editar')
            ->name('update');

        // Eliminación lógica
        Route::delete('/{vehiculo}', [VehiculoController::class, 'destroy'])
            ->middleware('can:vehiculos.eliminar')
            ->name('destroy');
        
        // Cambio de estado rápido (Activo/Inactivo/Taller)
        Route::patch('/{vehiculo}/toggle-status', [VehiculoController::class, 'toggleStatus'])
            ->middleware('can:vehiculos.editar')
            ->name('toggle-status');

        // Restauración de vehículo
        Route::post('/{id}/restore', [VehiculoController::class, 'restore'])
            ->middleware('can:vehiculos.eliminar')
            ->name('restore');
    });

    if (app()->runningInConsole()) {
            echo "api_taller_mecanico.php se está cargando\n";
    }
});