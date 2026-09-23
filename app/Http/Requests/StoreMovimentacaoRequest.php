<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimentacaoRequest extends FormRequest
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
            'contrato_id' => ['required', 'integer', 'exists:contratos,id'],
            'data' => ['required', 'date'],
            'tipo' => ['required', Rule::in(['RETIRADA', 'DEVOLUCAO'])],
            'qtd' => ['required', 'integer', 'min:1'],
        ];
    }
}
