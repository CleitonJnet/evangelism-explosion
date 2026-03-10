<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'filter.max' => __('O filtro pode ter no máximo :max caracteres.', ['max' => 100]),
        ];
    }

    public function filterTerm(): ?string
    {
        $filter = trim((string) $this->validated('filter', ''));

        return $filter !== '' ? $filter : null;
    }
}
