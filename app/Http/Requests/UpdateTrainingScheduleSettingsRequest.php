<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTrainingScheduleSettingsRequest extends FormRequest
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
            'welcome_duration_minutes' => ['nullable', 'integer', 'min:30', 'max:60'],
            'schedule_settings' => ['required', 'array'],
            'schedule_settings.after_lunch_pause_minutes' => ['required', 'integer', 'min:5', 'max:10'],
            'schedule_settings.meals' => ['required', 'array'],
            'schedule_settings.meals.breakfast.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.breakfast.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.lunch.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.lunch.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.afternoon_snack.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.afternoon_snack.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.dinner.enabled' => ['required', 'boolean'],
            'schedule_settings.meals.dinner.duration_minutes' => ['required', 'integer', 'min:5', 'max:180'],
            'schedule_settings.meals.dinner.substitute_snack' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'welcome_duration_minutes.min' => 'A duração das boas-vindas deve ser de no mínimo 30 minutos.',
            'welcome_duration_minutes.max' => 'A duração das boas-vindas deve ser de no máximo 60 minutos.',
            'schedule_settings.after_lunch_pause_minutes.min' => 'A pausa após o almoço deve ter no mínimo 5 minutos.',
            'schedule_settings.after_lunch_pause_minutes.max' => 'A pausa após o almoço deve ter no máximo 10 minutos.',
            'schedule_settings.meals.*.duration_minutes.min' => 'A duração informada deve ser de ao menos 5 minutos.',
            'schedule_settings.meals.*.duration_minutes.max' => 'A duração informada deve ser de no máximo 180 minutos.',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'welcome_duration_minutes' => 'boas-vindas',
            'schedule_settings.after_lunch_pause_minutes' => 'pausa após o almoço',
        ];
    }
}
