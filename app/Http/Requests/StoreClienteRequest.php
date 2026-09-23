<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'nome' => ['required', 'string', 'max:255'],
            'cpf' => ['nullable', 'string', 'max:14', Rule::unique('clientes', 'cpf')],
            'telefone' => [
                'required',
                'string',
                'max:15',
                Rule::unique('clientes', 'telefone'),
            ],
            'endereco' => ['nullable', 'string', 'max:255'],
        ];
    }
}
