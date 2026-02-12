<?php

namespace App\Http\Controllers\Web;

use App\Helpers\DayScheduleHelper;
use App\Http\Controllers\Controller;
use App\Models\Training;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('pages.web.home');
    }

    public function donate(): View
    {
        return view('pages.web.donate');
    }

    public function faith(): View
    {
        return view('pages.web.about.faith');
    }

    public function history(): View
    {
        return view('pages.web.about.history');
    }

    public function vision_mission(): View
    {
        return view('pages.web.about.vision-mission');
    }

    public function everyday_evangelism(): View
    {
        return view('pages.web.ministry.everyday-evangelism');
    }

    public function kids_ee(): View
    {
        return view('pages.web.ministry.kids-ee');
    }

    public function schedule(): View
    {
        return view('pages.web.events.schedule-request');
    }

    public function events(): View
    {
        return view('pages.web.events.index');
    }

    public function details(string $id): View
    {
        $event = Training::query()
            ->with([
                'course',
                'church',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
                'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            ])
            ->findOrFail($id);

        $workloadMinutes = $event->eventDates->reduce(function (int $total, $eventDate): int {
            if (! $eventDate->start_time || ! $eventDate->end_time) {
                return $total;
            }

            $start = Carbon::parse($eventDate->date.' '.$eventDate->start_time);
            $end = Carbon::parse($eventDate->date.' '.$eventDate->end_time);

            if ($end->lessThanOrEqualTo($start)) {
                return $total;
            }

            return $total + $start->diffInMinutes($end);
        }, 0);

        $workloadDuration = null;
        if ($workloadMinutes > 0) {
            $hours = intdiv($workloadMinutes, 60);
            $minutes = $workloadMinutes % 60;
            $workloadDuration = $minutes > 0
                ? sprintf('%02dh%02d', $hours, $minutes)
                : sprintf('%02dh', $hours);
        }

        $canAccessPublicSchedule = $this->hasScheduleMatchForAllDays($event);

        return view('pages.web.events.details', compact('event', 'workloadDuration', 'canAccessPublicSchedule'));
    }

    public function downloadBanner(string $id): StreamedResponse
    {
        $event = Training::query()
            ->select(['id', 'banner'])
            ->findOrFail($id);

        $bannerPath = is_string($event->banner) ? trim($event->banner) : '';
        $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
        $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'bmp', 'svg'];
        $hasBannerImage = $bannerPath !== ''
            && in_array($bannerExtension, $allowedImageExtensions, true)
            && Storage::disk('public')->exists($bannerPath);

        if (! $hasBannerImage) {
            abort(404);
        }

        $disk = Storage::disk('public');
        $mimeType = $disk->mimeType($bannerPath) ?: 'application/octet-stream';
        $fileContents = $disk->get($bannerPath);

        return response()->streamDownload(static function () use ($fileContents): void {
            echo $fileContents;
        }, basename($bannerPath), ['Content-Type' => $mimeType]);
    }

    public function register(string $id)
    {
        $event = Training::query()
            ->with([
                'course',
                'church',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->findOrFail($id);

        return view('pages.web.events.register', compact('event'));
    }

    public function login(string $id)
    {
        $event = Training::query()
            ->with([
                'course',
                'church',
                'teacher',
                'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            ])
            ->findOrFail($id);

        return view('pages.web.events.login', compact('event'));
    }

    public function clinic_base(): View
    {
        return view('pages.web.events.clinic-base');
    }

    private function hasScheduleMatchForAllDays(Training $event): bool
    {
        return DayScheduleHelper::hasAllDaysMatch($event->eventDates, $event->scheduleItems);
    }
}
