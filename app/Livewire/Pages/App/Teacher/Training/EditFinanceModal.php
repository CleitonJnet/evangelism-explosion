<?php

namespace App\Livewire\Pages\App\Teacher\Training;

use App\Helpers\MoneyHelper;
use App\Models\Training;
use App\Models\TrainingFinanceAudit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditFinanceModal extends Component
{
    use WithFileUploads;

    public Training $training;

    public int $trainingId;

    public bool $showModal = false;

    public bool $busy = false;

    public ?string $price = null;

    public ?string $price_church = '0,00';

    public ?string $discount = '0,00';

    public mixed $pixQrCodeUpload = null;

    public ?string $pix_key = null;

    public function mount(int $trainingId): void
    {
        $this->trainingId = $trainingId;
        $this->loadTraining();
        $this->fillFromTraining();
    }

    #[On('open-edit-finance-modal')]
    public function openModal(int $trainingId): void
    {
        if ($trainingId !== $this->training->id) {
            abort(404);
        }

        $this->authorizeTraining($this->training);

        $this->training = Training::query()->findOrFail($this->training->id);
        $this->fillFromTraining();
        $this->resetValidation();
        $this->showModal = true;
    }

    public function closeModal(): void
    {
        $this->showModal = false;
        $this->resetValidation();
        $this->pixQrCodeUpload = null;
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
            DB::transaction(function () use ($validated): void {
                $actorId = Auth::id();
                $before = $this->financeSnapshot($this->training);

                $updates = [
                    'price_church' => $validated['price_church'] ?? null,
                    'discount' => $validated['discount'] ?? null,
                    'pix_key' => filled($validated['pix_key'] ?? null)
                        ? trim((string) $validated['pix_key'])
                        : null,
                ];

                if ($this->pixQrCodeUpload) {
                    $path = $this->pixQrCodeUpload->store("training-pix-qrcodes/{$this->training->id}", 'public');
                    $updates['pix_qr_code'] = $path;
                }

                $this->training->update($updates);

                if (! $actorId) {
                    return;
                }

                $after = $this->financeSnapshot($this->training->fresh());
                $changes = $this->resolveFinanceChanges($before, $after);

                if ($changes === []) {
                    return;
                }

                TrainingFinanceAudit::query()->create([
                    'training_id' => $this->training->id,
                    'user_id' => $actorId,
                    'changes' => $changes,
                ]);
            });

            $this->training = Training::query()->findOrFail($this->training->id);
            $this->fillFromTraining();
            $this->closeModal();
            $this->dispatch('training-finance-updated', trainingId: $this->training->id);
        } finally {
            $this->busy = false;
        }
    }

    public function getFinalPricePerRegistrationProperty(): string
    {
        $price = MoneyHelper::toFloat($this->training->getRawOriginal('price')) ?? 0.0;
        $priceChurch = MoneyHelper::toFloat($this->price_church) ?? 0.0;
        $discount = MoneyHelper::toFloat($this->discount) ?? 0.0;

        $total = $price + $priceChurch - $discount;

        return number_format($total, 2, ',', '.');
    }

    public function render(): View
    {
        $latestFinanceAudit = $this->training
            ->financeAudits()
            ->with('user:id,name,email')
            ->latest('id')
            ->first();

        return view('livewire.pages.app.teacher.training.edit-finance-modal', [
            'currentPixQrCodeUrl' => $this->training->pixQrCodeUrlForPayment(),
            'latestFinanceAudit' => $latestFinanceAudit,
        ]);
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'price_church' => ['nullable', 'string', 'max:50'],
            'discount' => ['nullable', 'string', 'max:50'],
            'pixQrCodeUpload' => ['nullable', 'image', 'max:5120'],
            'pix_key' => ['nullable', 'string', 'max:255'],
        ];
    }

    private function loadTraining(): void
    {
        $this->training = Training::query()->findOrFail($this->trainingId);
        $this->authorizeTraining($this->training);
    }

    private function fillFromTraining(): void
    {
        $this->price = $this->formatRawMoney($this->training->getRawOriginal('price'), null);
        $this->price_church = $this->formatRawMoney($this->training->getRawOriginal('price_church'), '0,00');
        $this->discount = $this->formatRawMoney($this->training->getRawOriginal('discount'), '0,00');
        $this->pix_key = $this->training->pix_key;
    }

    private function authorizeTraining(Training $training): void
    {
        Gate::authorize('access-teacher');

        if (Auth::id() !== $training->teacher_id) {
            abort(403);
        }
    }

    private function formatRawMoney(mixed $rawValue, ?string $default = '0,00'): ?string
    {
        $floatValue = MoneyHelper::toFloat($rawValue);

        if ($floatValue === null) {
            return $default;
        }

        return number_format($floatValue, 2, ',', '.');
    }

    /**
     * @return array<string, string|null>
     */
    private function financeSnapshot(Training $training): array
    {
        return [
            'price_church' => $training->getRawOriginal('price_church'),
            'discount' => $training->getRawOriginal('discount'),
            'pix_key' => $training->getRawOriginal('pix_key'),
            'pix_qr_code' => $training->getRawOriginal('pix_qr_code'),
        ];
    }

    /**
     * @param  array<string, string|null>  $before
     * @param  array<string, string|null>  $after
     * @return array<string, array{before: string|null, after: string|null}>
     */
    private function resolveFinanceChanges(array $before, array $after): array
    {
        $changes = [];

        foreach ($before as $field => $beforeValue) {
            $afterValue = $after[$field] ?? null;

            if ((string) $beforeValue === (string) $afterValue) {
                continue;
            }

            $changes[$field] = [
                'before' => $beforeValue,
                'after' => $afterValue,
            ];
        }

        return $changes;
    }
}
