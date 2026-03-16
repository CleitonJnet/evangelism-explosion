<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TrainingIndexFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'filter' => ['nullable', 'string', 'max:100'],
            'assignment' => ['nullable', Rule::in(['all', 'lead_teacher', 'assistant_teacher', 'mentor'])],
            'church' => ['nullable', 'string', 'max:100'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filter.max' => __('O filtro pode ter no maximo :max caracteres.', ['max' => 100]),
            'church.max' => __('A igreja pode ter no maximo :max caracteres.', ['max' => 100]),
            'assignment.in' => __('Selecione um tipo de atuacao valido.'),
            'from.date' => __('Informe uma data inicial valida.'),
            'to.date' => __('Informe uma data final valida.'),
            'to.after_or_equal' => __('A data final precisa ser igual ou posterior a data inicial.'),
        ];
    }

    public function filterTerm(): ?string
    {
        $filter = trim((string) $this->validated('filter', ''));

        return $filter !== '' ? $filter : null;
    }

    /**
     * @return array{
     *     filter: ?string,
     *     assignment: string,
     *     church: ?string,
     *     from: ?string,
     *     to: ?string
     * }
     */
    public function filters(): array
    {
        $church = trim((string) $this->validated('church', ''));

        return [
            'filter' => $this->filterTerm(),
            'assignment' => (string) ($this->validated('assignment') ?? 'all'),
            'church' => $church !== '' ? $church : null,
            'from' => $this->dateValue('from'),
            'to' => $this->dateValue('to'),
        ];
    }

    private function dateValue(string $key): ?string
    {
        $value = trim((string) $this->validated($key, ''));

        return $value !== '' ? $value : null;
    }
}
