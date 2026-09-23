<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContratoRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $dataInicio = $this->filled('data_inicio')
            ? $this->input('data_inicio')
            : now()->toDateString();

        $this->merge([
            'data_inicio' => $dataInicio,
            'ultima_cobranca' => $this->filled('ultima_cobranca')
                ? $this->input('ultima_cobranca')
                : $dataInicio,
            'proxima_cobranca' => $this->filled('proxima_cobranca')
                ? $this->input('proxima_cobranca')
                : Carbon::parse($dataInicio)->addDays(15)->toDateString(),
            'data_fim' => $this->input('data_fim') ?: null,
            'data_fim_manual' => $this->boolean('data_fim_manual'),
        ]);
    }

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
            'cliente_id' => ['required', 'integer', 'exists:clientes,id'],
            'qtd_inicial' => ['required', 'integer', 'min:0'],
            'endereco' => ['nullable', 'string', 'max:255'],
            'data_inicio' => ['required', 'date'],
            'data_fim' => ['nullable', 'date', 'after_or_equal:data_inicio'],
            'data_fim_manual' => ['sometimes', 'boolean'],
            'valor_pc_dia' => ['required', 'numeric', 'min:0'],
            'qtd_frete' => ['nullable', 'integer', 'min:0'],
            'valor_frete' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['ATIVO', 'BLOQUEADO'])],
            'ultima_cobranca' => ['date'],
            'proxima_cobranca' => ['required', 'date'],
            'obs' => ['nullable', 'string', 'max:255'],
        ];
    }
}
