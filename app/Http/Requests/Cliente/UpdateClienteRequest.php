<?php
// app/Http/Requests/Cliente/UpdateClienteRequest.php
namespace App\Http\Requests\Cliente;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        
        $clienteId = $this->route('cliente'); 

        return [
            'persona_id'     => [
                'required', 
                'exists:personas,id', 
                Rule::unique('clientes', 'persona_id')->ignore($clienteId)
            ],
            'codigo_cliente' => [
                'required', 
                'string', 
                'max:50', 
                Rule::unique('clientes', 'codigo_cliente')->ignore($clienteId)
            ],
            'activo'         => ['boolean'],
        ];
    }
}