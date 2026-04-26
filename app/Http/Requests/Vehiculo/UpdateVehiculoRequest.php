<?php
// app/Http/Requests/Vehiculo/UpdateVehiculoRequest.php
namespace App\Http\Requests\Vehiculo;

use App\Enums\VehiculoEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateVehiculoRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $vehiculoId = $this->route('vehiculo');

        return [
            'cliente_id'  => ['required', 'exists:clientes,id'],
            'sucursal_id' => ['required', 'exists:sucursales,id'],
            'placa'       => [
                'required', 
                'string', 
                'max:20', 
                Rule::unique('vehiculos', 'placa')->ignore($vehiculoId)
            ],
            'vin'         => ['nullable', 'string', 'max:50'],
            'marca'       => ['required', 'string', 'max:100'],
            'modelo'      => ['required', 'string', 'max:100'],
            'anio'        => ['required', 'integer', 'min:1900', 'max:' . (date('Y') + 1)],
            'color'       => ['nullable', 'string', 'max:50'],
            'tipo'        => ['required', 'string', 'max:50'],
            'kilometraje' => ['nullable', 'integer', 'min:0'],
            'estado'      => [new Enum(VehiculoEnum::class)],
        ];
    }
}