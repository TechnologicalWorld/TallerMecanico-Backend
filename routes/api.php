<?php

use App\Http\Controllers\Api\V1\ArchivoController;
use App\Http\Controllers\Api\V1\AsignacionController;
use App\Http\Controllers\Api\V1\AuditoriaController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\ContactoController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\DomicilioController;
use App\Http\Controllers\Api\V1\PermissionController;
use App\Http\Controllers\Api\V1\PersonaController;
use App\Http\Controllers\Api\V1\RoleController;
use App\Http\Controllers\Api\V1\SucursalController;
use App\Http\Controllers\Api\V1\UserController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {
        Route::post('login', [AuthController::class, 'login']);
        Route::post('register', [AuthController::class, 'register']);
    });

    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('auth')->group(function () {
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me'])->middleware('branch.active');
            Route::put('change-password', [AuthController::class, 'changePassword']);

            Route::get('sessions', [AuthController::class, 'sessions']);
            Route::delete('sessions/{id}', [AuthController::class, 'revokeSession']);
            Route::delete('sessions', [AuthController::class, 'revokeAllSessions']);
        });
    });

    Route::middleware('auth:sanctum')->prefix('users')->name('user.')->group(function () {

        Route::middleware(['branch.active'])->group(function () {
            Route::get('branches', [UserController::class, 'branches'])->name('branches');
            Route::put('switch-branch/{id}', [UserController::class, 'switchBranch'])->name('switch-branch');
            Route::post('switch-branch', [UserController::class, 'switchBranchPost'])->name('switch-branch.post');

            Route::get('/', [UserController::class, 'index'])->middleware('can:usuarios.ver');
            Route::post('/', [UserController::class, 'store'])->middleware('can:usuarios.crear');
            Route::get('/{id}', [UserController::class, 'show'])->middleware('can:usuarios.ver');
            Route::put('/{id}', [UserController::class, 'update'])->middleware('can:usuarios.editar');
            Route::patch('/{id}/toggle-status', [UserController::class, 'toggleStatus'])->middleware('can:usuarios.editar');
            Route::post('/{id}/assign-role',[UserController::class,'assignRole']);
            Route::delete('/{id}/remove-role',[UserController::class,'removeRole']);

        });
    });

    Route::middleware('auth:sanctum')->prefix('dashboard')->name('dashboard.')->group(function () {
        Route::middleware(['branch.active'])->group(function () {
            Route::get('meKAE', [AuthController::class, 'me']);
            Route::middleware('can:dashboard.ver')->group(function () {
                Route::get('/', [DashboardController::class, 'index'])->name('index');
            });

            Route::get('/summary', [DashboardController::class, 'summary'])->name('summary');

            Route::middleware('can:dashboard.ver_sucursal')->group(function () {
                Route::get('/branch/{id}', [DashboardController::class, 'branch'])->name('branch');
            });
        });
    });

    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {

        Route::prefix('personas')->group(function () {
            Route::get('/fisicas', [PersonaController::class, 'fisicas']);
            Route::get('/morales', [PersonaController::class, 'morales']);
            Route::patch('/{id}/estado', [PersonaController::class, 'changeEstado']);
            Route::post('/{id}/restore', [PersonaController::class, 'restore']);
        });
        Route::apiResource('personas', PersonaController::class)->except(['create', 'edit']);
    });
    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::apiResource('contactos', ContactoController::class)->except(['index']);
        Route::prefix('{tipo}/{id}')->group(function () {
            Route::get('contactos', [ContactoController::class, 'index']);
        });
    });

    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::apiResource('domicilios', DomicilioController::class)->except(['index']);
        Route::post('domicilios/{id}/restore', [DomicilioController::class, 'restore']);
        Route::prefix('{tipo}/{id}')->group(function () {
            Route::get('domicilios', [DomicilioController::class, 'index']);
            Route::get('domicilio-principal', [DomicilioController::class, 'principal']);
        });
    });

    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::apiResource('archivos', ArchivoController::class)->except(['index', 'update']);
        Route::get('archivos/{id}/download', [ArchivoController::class, 'download']);
        Route::post('archivos/{id}/restore', [ArchivoController::class, 'restore']);
        Route::get('archivos/{id}/url', [ArchivoController::class, 'url']);
        Route::delete('archivos/{id}/force', [ArchivoController::class, 'forceDestroy'])
            ->middleware('can:archivos.eliminar_permanente');

        Route::prefix('{tipo}/{id}')->group(function () {
            Route::get('archivos', [ArchivoController::class, 'index']);
        });
    });

    Route::prefix('sucursales')->name('sucursales.')->group(function () {
        Route::middleware(['branch.active'])->group(function () {
            Route::get('/selector', [SucursalController::class, 'getParaSelector'])->middleware('can:sucursales.ver');
            Route::get('/verificar-codigo/{codigo}', [SucursalController::class, 'verificarCodigo'])->middleware('can:sucursales.ver');

            Route::get('/', [SucursalController::class, 'index'])->middleware('can:sucursales.ver');
            Route::post('/', [SucursalController::class, 'store'])->middleware('can:sucursales.crear');
            Route::get('/{id}', [SucursalController::class, 'show'])->middleware('can:sucursales.ver');
            Route::put('/{id}', [SucursalController::class, 'update'])->middleware('can:sucursales.editar');
            Route::patch('/{id}/toggle-status', [SucursalController::class, 'toggleStatus'])->middleware('can:sucursales.editar');
            Route::get('/{id}/usuarios', [SucursalController::class, 'getUsuarios'])->middleware('can:sucursales.ver');
        });
    });

    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::prefix('roles')->name('roles.')->group(function () {
            Route::get('/', [RoleController::class, 'index'])->middleware('can:roles.ver');
            Route::get('/{id}', [RoleController::class, 'show'])->middleware('can:roles.ver');
            Route::post('/', [RoleController::class, 'store'])->middleware('can:roles.crear');
            Route::put('/{id}', [RoleController::class, 'update'])->middleware('can:roles.editar');
            Route::delete('/{id}', [RoleController::class, 'destroy'])->middleware('can:roles.eliminar');
            Route::post('/{id}/sync-permissions', [RoleController::class, 'syncPermissions'])->middleware('can:roles.editar');
            Route::delete('/{id}/remove-rol',[RoleController::class,'removePermission']);
        });
    });

    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::prefix('permisos')->name('permisos.')->group(function () {
            Route::get('/', [PermissionController::class, 'index'])->middleware('can:permisos.ver');
            Route::get('/agrupados', [PermissionController::class, 'agrupados'])->middleware('can:permisos.ver');
            Route::get('/{id}', [PermissionController::class, 'show'])->middleware('can:permisos.ver');
            Route::post('/', [PermissionController::class, 'store'])->middleware('can:permisos.crear');
            Route::put('/{id}', [PermissionController::class, 'update'])->middleware('can:permisos.editar');
            Route::delete('/{id}', [PermissionController::class, 'destroy'])->middleware('can:permisos.eliminar');
        });
    });


    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::prefix('auditoria')->name('auditoria.')->group(function () {
            Route::get('/', [AuditoriaController::class, 'index'])->middleware('can:auditoria.ver');
            Route::get('/acciones', [AuditoriaController::class, 'acciones'])->middleware('can:auditoria.ver');
            Route::get('/entidades', [AuditoriaController::class, 'entidades'])->middleware('can:auditoria.ver');
            Route::get('/exportar', [AuditoriaController::class, 'exportar'])->middleware('can:auditoria.exportar');
            Route::get('/{id}', [AuditoriaController::class, 'show'])->middleware('can:auditoria.ver');
        });
    });



    Route::middleware(['auth:sanctum', 'branch.active'])->group(function () {
        Route::prefix('asignaciones')->name('asignaciones.')->group(function () {
            Route::post('/', [AsignacionController::class, 'store'])->middleware('can:asignaciones.asignar');
            Route::delete('/{idSucursal}/{idUsuario}', [AsignacionController::class, 'destroy'])->middleware('can:asignaciones.quitar');
        });
    });
});
