<?php

namespace App\Livewire\Pages\App\Director\Website\Testimonials;

use App\Models\Testimonial;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\WithFileUploads;

class CreateModal extends Component
{
    use WithFileUploads;

    public bool $showModal = false;

    public string $name = '';

    public string $meta = '';

    public string $quote = '';

    public bool $isActive = true;

    public mixed $photoUpload = null;

    public function openModal(): void
    {
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetForm();
    }

    public function save(): void
    {
        $validated = $this->validate($this->rules(), $this->messages(), $this->validationAttributes());

        $position = (int) Testimonial::query()->max('position') + 1;

        Testimonial::query()->create([
            'name' => trim($validated['name']),
            'meta' => trim((string) $validated['meta']) ?: null,
            'quote' => trim($validated['quote']),
            'photo' => $this->storeUploadedPhoto(),
            'position' => $position,
            'is_active' => (bool) $validated['isActive'],
        ]);

        $this->dispatch('testimonial-created');
        $this->closeModal();
    }

    public function render(): View
    {
        return view('livewire.pages.app.director.website.testimonials.create-modal');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:120'],
            'meta' => ['nullable', 'string', 'max:180'],
            'quote' => ['required', 'string', 'max:250'],
            'photoUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'isActive' => ['boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'name.required' => 'Informe o nome para exibicao.',
            'quote.required' => 'Informe o testemunho.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'name' => 'nome',
            'meta' => 'cargo/igreja',
            'quote' => 'testemunho',
            'photoUpload' => 'foto',
            'isActive' => 'status',
        ];
    }

    private function resetForm(): void
    {
        $this->reset([
            'name',
            'meta',
            'quote',
            'photoUpload',
            'isActive',
        ]);

        $this->isActive = true;
        $this->resetValidation();
    }

    private function storeUploadedPhoto(): ?string
    {
        if (! $this->photoUpload) {
            return null;
        }

        return $this->photoUpload->store('testimonials/photos', 'public');
    }
}
