<?php

namespace App\Services\Training;

use App\Models\Church;
use App\Models\Course;

class TeacherTrainingCreateService
{
    public function normalizeStep(int $step, int $maxStep = 5): int
    {
        return max(1, min($maxStep, $step));
    }

    public function resolveCoursePrice(int|string|null $courseId): ?string
    {
        if (! $courseId) {
            return null;
        }

        return Course::query()->find($courseId)?->price;
    }

    public function churchExists(int $churchId): bool
    {
        return Church::query()->whereKey($churchId)->exists();
    }

    /**
     * @return array{
     *     address: array{postal_code: string, street: string, number: string, complement: string, district: string, city: string, state: string},
     *     phone: ?string,
     *     email: ?string,
     *     gpwhatsapp: ?string,
     *     coordinator: ?string
     * }|null
     */
    public function resolveChurchSelectionData(int $churchId): ?array
    {
        $church = Church::query()->find($churchId);

        if (! $church) {
            return null;
        }

        return [
            'address' => [
                'postal_code' => $church->postal_code ?? '',
                'street' => $church->street ?? '',
                'number' => $church->number ?? '',
                'complement' => $church->complement ?? '',
                'district' => $church->district ?? '',
                'city' => $church->city ?? '',
                'state' => $church->state ?? '',
            ],
            'phone' => $church->phone,
            'email' => $church->email,
            'gpwhatsapp' => $church->contact_phone,
            'coordinator' => $church->contact,
        ];
    }
}
