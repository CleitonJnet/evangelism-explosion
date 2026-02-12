<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Models\TrainingScheduleItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class PublicEventScheduleController extends Controller
{
    public function show(Training $training): View
    {
        $this->loadTrainingRelations($training);

        return view('pages.web.events.schedule', $this->buildScheduleViewModel($training));
    }

    public function pdf(Training $training): Response
    {
        $this->loadTrainingRelations($training);

        $viewModel = $this->buildScheduleViewModel($training);
        $viewModel['logoDataUri'] = $this->resolvePdfLogoDataUri();
        $viewModel['generatedAt'] = now('America/Sao_Paulo');
        $viewModel['pdfColumns'] = $this->buildPdfColumns($viewModel['scheduleDays']);

        $pdf = Pdf::loadView('pdf.event-schedule', $viewModel)
            ->setPaper('a4')
            ->setCallbacks([
                [
                    'event' => 'end_document',
                    'f' => static function (int $pageNumber, int $pageCount, $canvas, $fontMetrics): void {
                        $font = $fontMetrics->getFont('Helvetica', 'normal');
                        $canvas->page_text(
                            486,
                            815,
                            sprintf('Pagina %d de %d', $pageNumber, $pageCount),
                            $font,
                            9,
                            [0.4706, 0.6275, 0.7647],
                        );
                    },
                ],
            ]);

        return $pdf->download('programacao-evento-'.$training->id.'.pdf');
    }

    /**
     * @return array{
     *     training: Training,
     *     datesSummary: string,
     *     eventDates: Collection<int, mixed>,
     *     scheduleDays: array<int, array{
     *         date: string,
     *         weekdayLabel: string,
     *         dayLabel: string,
     *         groups: array<int, array{
     *             turn: string,
     *             heading: string,
     *             items: array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>
     *         }>
     *     }>
     * }
     */
    private function buildScheduleViewModel(Training $training): array
    {
        $eventDates = $training->eventDates
            ->sortBy([
                ['date', 'asc'],
                ['start_time', 'asc'],
            ])
            ->values();

        $scheduleByDay = $training->scheduleItems
            ->sortBy([
                fn (TrainingScheduleItem $item): string => (string) $item->date?->format('Y-m-d'),
                fn (TrainingScheduleItem $item): int => (int) $item->starts_at?->getTimestamp(),
                fn (TrainingScheduleItem $item): int => (int) $item->position,
            ])
            ->groupBy(fn (TrainingScheduleItem $item): string => (string) $item->date?->format('Y-m-d'))
            ->sortKeys();

        $scheduleDays = [];

        $eventDateStartsByDay = $eventDates
            ->mapWithKeys(fn ($eventDate): array => [(string) $eventDate->date => (string) ($eventDate->start_time ?? '')])
            ->all();

        foreach ($scheduleByDay as $date => $items) {
            if ($date === '') {
                continue;
            }

            $weekdayLabel = $this->weekdayLabel($date);
            $dayLabel = $weekdayLabel.', '.Carbon::parse($date)->format('d/m');
            $turnGroups = [
                'MANHA' => [],
                'TARDE' => [],
                'NOITE' => [],
            ];

            /** @var TrainingScheduleItem $item */
            foreach ($items as $item) {
                $turn = $this->resolveTurn($item, $eventDateStartsByDay[$date] ?? null);

                $turnGroups[$turn][] = [
                    'startsAtTimestamp' => $item->starts_at?->getTimestamp() ?? PHP_INT_MAX,
                    'timeRange' => $this->timeRange($item),
                    'title' => $item->title ?: '--',
                    'subtitle' => $item->section?->name ?? $this->formatTypeLabel($item->type),
                    'devotional' => $item->section?->devotional,
                    'duration' => $this->durationLabel($item),
                ];
            }

            $nonEmptyTurns = collect($turnGroups)
                ->filter(fn (array $turnItems): bool => $turnItems !== []);

            $groups = [];

            if ($nonEmptyTurns->count() <= 1) {
                $singleTurnKey = (string) ($nonEmptyTurns->keys()->first() ?? 'UNICO');
                $singleTurnItems = $this->sortGroupItems($nonEmptyTurns->first() ?? []);

                $groups[] = [
                    'turn' => $singleTurnKey,
                    'heading' => $dayLabel,
                    'items' => $singleTurnItems,
                ];
            } else {
                $orderedTurns = $nonEmptyTurns
                    ->map(fn (array $turnItems, string $turn): array => [
                        'turn' => $turn,
                        'firstStartsAt' => (int) collect($turnItems)
                            ->min(fn (array $entry): int => (int) ($entry['startsAtTimestamp'] ?? PHP_INT_MAX)),
                        'items' => $this->sortGroupItems($turnItems),
                    ])
                    ->sortBy(fn (array $group): int => $group['firstStartsAt'])
                    ->values();

                foreach ($orderedTurns as $groupData) {
                    $turn = (string) $groupData['turn'];

                    $groups[] = [
                        'turn' => $turn,
                        'heading' => $dayLabel.' - '.$this->turnLabel($turn),
                        'items' => $groupData['items'],
                    ];
                }
            }

            $scheduleDays[] = [
                'date' => $date,
                'weekdayLabel' => $weekdayLabel,
                'dayLabel' => $dayLabel,
                'groups' => $groups,
            ];
        }

        return [
            'training' => $training,
            'datesSummary' => $this->datesSummary($eventDates),
            'eventDates' => $eventDates,
            'scheduleDays' => $scheduleDays,
        ];
    }

    private function loadTrainingRelations(Training $training): void
    {
        $training->load([
            'course',
            'church',
            'teacher',
            'eventDates' => fn ($query) => $query->orderBy('date')->orderBy('start_time'),
            'scheduleItems' => fn ($query) => $query->orderBy('date')->orderBy('starts_at')->orderBy('position'),
            'scheduleItems.section',
        ]);
    }

    /**
     * @param  Collection<int, mixed>  $eventDates
     */
    private function datesSummary(Collection $eventDates): string
    {
        if ($eventDates->isEmpty()) {
            return '--';
        }

        $firstDate = Carbon::parse((string) $eventDates->first()->date);
        $lastDate = Carbon::parse((string) $eventDates->last()->date);

        if ($firstDate->isSameDay($lastDate)) {
            return $firstDate->format('d/m/Y');
        }

        return $firstDate->format('d/m/Y').' a '.$lastDate->format('d/m/Y');
    }

    private function weekdayLabel(string $date): string
    {
        return mb_strtoupper(Carbon::parse($date)->locale('pt_BR')->isoFormat('dddd'), 'UTF-8');
    }

    private function resolveTurn(TrainingScheduleItem $item, ?string $fallbackStartTime): string
    {
        $hour = $item->starts_at ? (int) $item->starts_at->format('H') : null;
        if ($hour === null && $fallbackStartTime) {
            $hour = (int) Carbon::parse($fallbackStartTime)->format('H');
        }

        $hour ??= 0;

        if ($hour >= 5 && $hour <= 11) {
            return 'MANHA';
        }

        if ($hour >= 12 && $hour <= 17) {
            return 'TARDE';
        }

        return 'NOITE';
    }

    private function turnLabel(string $turn): string
    {
        return match ($turn) {
            'MANHA' => 'MANHA',
            'TARDE' => 'TARDE',
            default => 'NOITE',
        };
    }

    private function timeRange(TrainingScheduleItem $item): string
    {
        $start = $item->starts_at?->format('H:i') ?? '--:--';
        $end = $item->ends_at?->format('H:i') ?? '--:--';

        if ($start === '--:--' && $end === '--:--') {
            return '--:--';
        }

        return $start.' - '.$end;
    }

    private function formatTypeLabel(?string $type): string
    {
        if ($type === null || $type === '') {
            return '--';
        }

        return match ($type) {
            'WELCOME' => 'Boas-vindas',
            'SECTION' => 'Sessao',
            'MEAL' => 'Refeicao',
            'BREAK' => 'Intervalo',
            'OPENING' => 'Abertura',
            default => ucfirst(strtolower(str_replace('_', ' ', $type))),
        };
    }

    /**
     * @param  array<int, array{startsAtTimestamp?: int, timeRange: string, title: string, subtitle: string}>  $items
     * @return array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>
     */
    private function sortGroupItems(array $items): array
    {
        return collect($items)
            ->sortBy(fn (array $entry): int => (int) ($entry['startsAtTimestamp'] ?? PHP_INT_MAX))
            ->values()
            ->map(function (array $entry): array {
                unset($entry['startsAtTimestamp']);

                return $entry;
            })
            ->all();
    }

    private function durationLabel(TrainingScheduleItem $item): string
    {
        if (! $item->starts_at || ! $item->ends_at || $item->ends_at->lte($item->starts_at)) {
            return '--';
        }

        $minutes = $item->starts_at->diffInMinutes($item->ends_at);

        if ($minutes < 60) {
            return $minutes.'m';
        }

        $hours = intdiv($minutes, 60);
        $remainingMinutes = $minutes % 60;

        if ($remainingMinutes === 0) {
            return $hours.'h';
        }

        return $hours.'h '.$remainingMinutes.'m';
    }

    /**
     * @param  array<int, array{date: string, weekdayLabel: string, dayLabel: string, groups: array<int, array{turn: string, heading: string, items: array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>}>}>  $scheduleDays
     * @return array{
     *     left: array<int, array{heading: string, items: array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>}>,
     *     right: array<int, array{heading: string, items: array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>}>
     * }
     */
    private function buildPdfColumns(array $scheduleDays): array
    {
        $columns = ['left' => [], 'right' => []];
        $columnHeights = ['left' => 0, 'right' => 0];

        foreach ($scheduleDays as $day) {
            foreach ($day['groups'] as $group) {
                $blockHeight = $this->estimatePdfBlockHeight((array) $group['items']);
                $columnKey = $columnHeights['left'] <= $columnHeights['right'] ? 'left' : 'right';

                $columns[$columnKey][] = [
                    'heading' => $group['heading'],
                    'items' => $group['items'],
                ];

                $columnHeights[$columnKey] += $blockHeight;
            }
        }

        return $columns;
    }

    /**
     * @param  array<int, array{timeRange: string, title: string, subtitle: string, devotional: ?string, duration: string}>  $items
     */
    private function estimatePdfBlockHeight(array $items): int
    {
        $baseHeaderHeight = 90;
        $rowHeight = 30;

        return $baseHeaderHeight + (count($items) * $rowHeight);
    }

    private function resolvePdfLogoDataUri(): ?string
    {
        $cachedPngPath = storage_path('app/public/cache/ee-gold.png');
        if (is_file($cachedPngPath) && is_readable($cachedPngPath)) {
            $data = file_get_contents($cachedPngPath);
            if (is_string($data) && $data !== '') {
                return 'data:image/png;base64,'.base64_encode($data);
            }
        }

        $webpPath = public_path('images/logo/ee-gold.webp');
        if (! is_file($webpPath)) {
            return null;
        }

        if (! function_exists('imagecreatefromwebp') || ! function_exists('imagepng')) {
            return null;
        }

        $cacheDirectory = dirname($cachedPngPath);
        if (! is_dir($cacheDirectory)) {
            mkdir($cacheDirectory, 0755, true);
        }

        $image = @imagecreatefromwebp($webpPath);
        if ($image !== false) {
            imagepng($image, $cachedPngPath);
            imagedestroy($image);

            $pngData = file_get_contents($cachedPngPath);
            if (is_string($pngData) && $pngData !== '') {
                return 'data:image/png;base64,'.base64_encode($pngData);
            }
        }

        return null;
    }
}
