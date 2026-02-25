<?php

namespace App\Http\Requests;

use App\Services\Training\TestimonySanitizer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class UpdateTrainingTestimonyRequest extends FormRequest
{
    public function authorize(): bool
    {
        $training = $this->route('training');

        if (! $training) {
            return false;
        }

        return $this->user()?->can('update', $training) ?? false;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'notes' => ['nullable', 'string', 'max:50000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $length = TestimonySanitizer::plainTextLength($this->input('notes'));

            if ($length > TestimonySanitizer::MAX_CHARACTERS) {
                $validator->errors()->add(
                    'notes',
                    __('O relato pode ter no máximo :max caracteres.', ['max' => TestimonySanitizer::MAX_CHARACTERS]),
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'notes.max' => __('O conteúdo enviado excede o tamanho permitido.'),
        ];
    }
}
