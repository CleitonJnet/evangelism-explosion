<?php

namespace App\Livewire\Web\Home;

use App\Models\Testimonial;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Component;

class Testimonials extends Component
{
    public function render(): View
    {
        $testimonials = Testimonial::query()
            ->where('is_active', true)
            ->orderByDesc('position')
            ->orderByDesc('id')
            ->get()
            ->map(function (Testimonial $testimonial): array {
                return [
                    'photo' => $this->resolvePhotoUrl($testimonial->photo),
                    'quote' => $testimonial->quote,
                    'name' => $testimonial->name,
                    'meta' => $testimonial->meta ?: '',
                ];
            });

        return view('livewire.web.home.testimonials', compact('testimonials'));
    }

    private function resolvePhotoUrl(?string $photoPath): string
    {
        if (! is_string($photoPath) || trim($photoPath) === '') {
            return asset('images/profile.webp');
        }

        if (Str::startsWith($photoPath, ['http://', 'https://'])) {
            return $photoPath;
        }

        return Storage::disk('public')->exists($photoPath)
            ? Storage::disk('public')->url($photoPath)
            : asset('images/profile.webp');
    }
}
