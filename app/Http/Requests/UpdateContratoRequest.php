<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContratoRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['sometimes', 'integer', 'exists:clientes,id'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'data_inicio' => ['sometimes', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'valor_pc_dia' => ['sometimes', 'numeric', 'min:0'],
            'cobrar_sabado' => ['boolean'],
            'qtd_frete' => ['nullable', 'integer', 'min:0'],
            'desconto' => ['nullable', 'numeric', 'min:0'],
            'valor_frete' => ['sometimes', 'numeric', 'min:0'],
            'status' => ['sometimes', Rule::in(['ATIVO', 'BLOQUEADO', 'DEVOLVIDO', 'FINALIZADO'])],
            'ultima_cobranca' => ['sometimes', 'date'],
            'proxima_cobranca' => ['sometimes', 'date'],
            'obs' => ['nullable', 'string', 'max:255'],
        ];
    }

}
