<?php

namespace App\Services;

use App\Models\Archivo;
use App\Models\Auditoria;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArchivoService
{
    /**
     * Disco de almacenamiento por defecto
     */
    protected string $disk = 'public';

    /**
     * Listar archivos por entidad
     */
    public function listarPorEntidad(string $entidadType, int $entidadId, ?string $tipo = null)
    {
        $query = Archivo::where('entidad_type', $entidadType)
            ->where('entidad_id', $entidadId);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Subir archivo
     */
    public function subir(array $data, UploadedFile $archivo, int $usuarioId): Archivo
    {
        return DB::transaction(function () use ($data, $archivo, $usuarioId) {
            $extension = $archivo->getClientOriginalExtension();
            $nombreOriginal = $data['nombre'] ?? $archivo->getClientOriginalName();
            $nombreArchivo = Str::slug(pathinfo($nombreOriginal, PATHINFO_FILENAME))
                           .'_'.time()
                           .'_'.Str::random(10)
                           .'.'.$extension;

            $entidadAlias = class_basename($data['entidad_type']);
            $rutaBase = strtolower($entidadAlias).'s/'.$data['entidad_id'].'/'.strtolower($data['tipo']);

            $ruta = $archivo->storeAs($rutaBase, $nombreArchivo, $this->disk);

            $archivoModel = Archivo::create([
                'entidad_type' => $data['entidad_type'],
                'entidad_id' => $data['entidad_id'],
                'nombre' => $nombreOriginal,
                'ruta' => $ruta,
                'tipo' => $data['tipo'],
                'fecha_expiracion' => $data['fecha_expiracion'] ?? null,
            ]);

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ARCHIVO_SUBIDO',
                'entidad_type' => $data['entidad_type'],
                'entidad_id' => $data['entidad_id'],
                'valores_nuevos' => [
                    'archivo_id' => $archivoModel->id,
                    'nombre' => $nombreOriginal,
                    'tipo' => $data['tipo'],
                    'ruta' => $ruta,
                    'tamano' => $archivo->getSize(),
                ],
            ]);

            return $archivoModel;
        });
    }

    /**
     * Descargar archivo
     */
    public function descargar(int $id)
    {
        $archivo = Archivo::findOrFail($id);

        if (! Storage::disk($this->disk)->exists($archivo->ruta)) {
            throw new \Exception('El archivo no existe en el servidor');
        }

        return Storage::disk($this->disk)->download($archivo->ruta, $archivo->nombre, [
            'Content-Type' => Storage::mimeType($archivo->ruta),
        ]);
    }

    /**
     * Obtener URL pública del archivo
     */
    public function url(int $id): string
    {
        $archivo = Archivo::findOrFail($id);

        return Storage::disk($this->disk)->url($archivo->ruta);
    }

    /**
     * Restaurar archivo eliminado
     */
    public function restaurar(int $id, int $usuarioId): Archivo
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $archivo = Archivo::withTrashed()->findOrFail($id);
            $archivo->restore();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ARCHIVO_RESTAURADO',
                'entidad_type' => $archivo->entidad_type,
                'entidad_id' => $archivo->entidad_id,
                'valores_nuevos' => $archivo->toArray(),
            ]);

            return $archivo;
        });
    }


    /**
     * Eliminar archivo (lógico)
     */
    public function eliminar(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $archivo = Archivo::findOrFail($id);

            $infoArchivo = [
                'archivo_id' => $archivo->id,
                'nombre' => $archivo->nombre,
                'tipo' => $archivo->tipo,
                'ruta' => $archivo->ruta,
            ];

            // if (Storage::disk($this->disk)->exists($archivo->ruta)) {
            //     Storage::disk($this->disk)->delete($archivo->ruta);
            // }
            $archivo->delete();


            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ARCHIVO_ELIMINADO',
                'entidad_type' => $archivo->entidad_type,
                'entidad_id' => $archivo->entidad_id,
                'valores_anteriores' => $infoArchivo,
            ]);

            return true;
        });
    }

    /**
     * Eliminar archivo físico y de BD (hard delete)
     */
    public function eliminarPermanente(int $id, int $usuarioId): bool
    {
        return DB::transaction(function () use ($id, $usuarioId) {
            $archivo = Archivo::withTrashed()->findOrFail($id);

            if (Storage::disk($this->disk)->exists($archivo->ruta)) {
                Storage::disk($this->disk)->delete($archivo->ruta);
            }

            $archivo->forceDelete();

            Auditoria::create([
                'usuario_id' => $usuarioId,
                'accion' => 'ARCHIVO_ELIMINADO_PERMANENTE',
                'entidad_type' => $archivo->entidad_type,
                'entidad_id' => $archivo->entidad_id,
                'valores_anteriores' => ['archivo_id' => $id],
            ]);

            return true;
        });
    }

    /**
     * Limpiar archivos expirados (para comando de consola)
     */
    public function limpiarExpirados(): int
    {
        $archivos = Archivo::whereNotNull('fecha_expiracion')
            ->where('fecha_expiracion', '<', now())
            ->get();

        $count = 0;
        foreach ($archivos as $archivo) {
            if (Storage::disk($this->disk)->exists($archivo->ruta)) {
                Storage::disk($this->disk)->delete($archivo->ruta);
            }
            $archivo->delete();
            $count++;
        }

        return $count;
    }

    /**
     * Obtener estadísticas de archivos por entidad
     */
    public function estadisticasPorEntidad(string $entidadType, int $entidadId): array
    {
        $archivos = Archivo::where('entidad_type', $entidadType)
            ->where('entidad_id', $entidadId)
            ->get();

        $porTipo = $archivos->groupBy('tipo')->map->count();

        return [
            'total' => $archivos->count(),
            'por_tipo' => $porTipo,
        ];
    }
}
