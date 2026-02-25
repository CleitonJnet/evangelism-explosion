<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Models\Training;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditEventBannerModal extends Component
{
    use WithFileUploads;

    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public mixed $bannerUpload = null;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
    }

    #[On('open-edit-event-banner-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->trainingId) {
            abort(404);
        }

        $this->loadTraining();
        $this->resetValidation();
        $this->bannerUpload = null;
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->bannerUpload = null;
    }

    public function save(): void
    {
        if ($this->busy) {
            return;
        }

        $this->authorizeTraining($this->training);
        $validated = $this->validate();
        $this->busy = true;

        try {
            $path = $validated['bannerUpload']->store("training-banners/{$this->training->id}", 'public');

            $this->training->update([
                'banner' => $path,
            ]);

            $this->training->refresh();
            $this->closeModal();
            $this->dispatch('training-banner-updated', trainingId: $this->training->id);
        } finally {
            $this->busy = false;
        }
    }

    public function render(): View
    {
        return view('livewire.pages.app.teacher.training.edit-event-banner-modal', [
            'currentBannerUrl' => $this->currentBannerUrl(),
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'bannerUpload' => ['required', 'image', 'max:10240'],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function messages(): array
    {
        return [
            'bannerUpload.required' => 'Selecione uma imagem para o banner.',
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function validationAttributes(): array
    {
        return [
            'bannerUpload' => 'banner do evento',
        ];
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()->findOrFail($this->trainingId);
        $this->authorizeTraining($this->training);
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-teacher');

        if (Auth::id() !== $training->teacher_id) {
            abort(403);
        }
    }

    private function currentBannerUrl(): ?string
    {
        $bannerPath = trim((string) $this->training->banner);

        if ($bannerPath === '' || ! Storage::disk('public')->exists($bannerPath)) {
            return null;
        }

        return Storage::disk('public')->url($bannerPath);
    }
}
