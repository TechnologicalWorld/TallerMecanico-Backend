<?php
// app/Http/Requests/Cliente/StoreClienteRequest.php
namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; 
    }

    public function rules(): array
    {
        return [
            'persona_id'     => ['required', 'exists:personas,id', 'unique:clientes,persona_id'],
            'codigo_cliente' => ['required', 'string', 'max:50', 'unique:clientes,codigo_cliente'],
            'activo'         => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'persona_id.unique' => 'Esta persona ya está registrada como cliente.',
            'codigo_cliente.unique' => 'El código de cliente ya está en uso.',
        ];
    }
}