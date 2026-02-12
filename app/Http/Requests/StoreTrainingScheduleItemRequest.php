<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreTrainingScheduleItemRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'date' => ['required', 'date_format:Y-m-d'],
            'starts_at' => ['required', 'date_format:Y-m-d H:i:s'],
            'planned_duration_minutes' => ['required', 'integer', 'min:1', 'max:720'],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:50'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => 'A data é obrigatória.',
            'date.date_format' => 'A data deve estar no formato YYYY-MM-DD.',
            'starts_at.required' => 'O horário inicial é obrigatório.',
            'starts_at.date_format' => 'O horário inicial deve estar no formato YYYY-MM-DD HH:MM:SS.',
            'planned_duration_minutes.required' => 'A duração é obrigatória.',
            'planned_duration_minutes.integer' => 'A duração deve ser um número inteiro.',
            'planned_duration_minutes.min' => 'A duração deve ser de ao menos 1 minuto.',
            'planned_duration_minutes.max' => 'A duração deve ser de no máximo 720 minutos.',
            'title.required' => 'O título é obrigatório.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',
            'type.required' => 'O tipo é obrigatório.',
            'type.max' => 'O tipo deve ter no máximo 50 caracteres.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'date' => 'data',
            'starts_at' => 'horário inicial',
            'planned_duration_minutes' => 'duração',
            'title' => 'título',
            'type' => 'tipo',
        ];
    }
}
