<?php

namespace App\Http\Requests;

use App\Models\TrainingScheduleItem;
use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingScheduleItemRequest extends FormRequest
{
    private const MAX_SECTION_DURATION_MINUTES = 120;

    private const DURATION_STEP_MINUTES = 5;

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
            'planned_duration_minutes' => ['sometimes', 'integer', 'min:5', 'max:720', 'multiple_of:5'],
            'title' => ['sometimes', 'string', 'max:255'],
            'type' => ['sometimes', 'string', 'max:50'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator): void {
            if (! $this->has('planned_duration_minutes')) {
                return;
            }

            $item = $this->route('item');

            if (! $item instanceof TrainingScheduleItem) {
                return;
            }

            $suggested = (int) ($item->suggested_duration_minutes ?? 0);

            if (! $item->section_id || $suggested <= 0) {
                return;
            }

            $computedMin = (int) ceil($suggested * 0.8);
            $storedMin = max(0, (int) ($item->min_duration_minutes ?? 0));
            $baseMin = min(self::MAX_SECTION_DURATION_MINUTES, max($computedMin, $storedMin));
            $baseMax = min(self::MAX_SECTION_DURATION_MINUTES, (int) floor($suggested * 1.2));
            $min = max(self::DURATION_STEP_MINUTES, $this->roundDownToStep($baseMin));
            $max = max($min, $this->roundUpToStep($baseMax));
            $max = max($min, $max);
            $value = (int) $this->input('planned_duration_minutes');

            if ($value < $min || $value > $max) {
                $validator->errors()->add('planned_duration_minutes', 'A duração deve estar dentro de 20% do valor sugerido.');
            }
        });
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
            'planned_duration_minutes.integer' => 'A duração deve ser um número inteiro.',
            'planned_duration_minutes.min' => 'A duração deve ser de ao menos 5 minutos.',
            'planned_duration_minutes.max' => 'A duração deve ser de no máximo 720 minutos.',
            'planned_duration_minutes.multiple_of' => 'A duração deve ser informada em passos de 5 minutos.',
            'title.max' => 'O título deve ter no máximo 255 caracteres.',
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

    private function roundUpToStep(int $value): int
    {
        return (int) (ceil($value / self::DURATION_STEP_MINUTES) * self::DURATION_STEP_MINUTES);
    }

    private function roundDownToStep(int $value): int
    {
        return (int) (floor($value / self::DURATION_STEP_MINUTES) * self::DURATION_STEP_MINUTES);
    }
}
