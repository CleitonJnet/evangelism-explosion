<?php

namespace App\Http\Controllers\Web;

use App\Helpers\DayScheduleHelper;
use App\Http\Controllers\Controller;
use App\Models\Training;
use App\Services\Portals\PortalSessionManager;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SiteController extends Controller
{
    public function home(): View
    {
        return view('pages.web.home', [
            'portalCards' => $this->portalCards(),
        ]);
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
            ->with([
                'course:id,type,name',
                'eventDates' => fn ($query) => $query
                    ->select(['id', 'training_id', 'date'])
                    ->orderBy('date')
                    ->limit(1),
            ])
            ->select(['id', 'course_id', 'banner'])
            ->findOrFail($id);

        $bannerPath = is_string($event->banner) ? trim($event->banner) : '';
        $bannerExtension = strtolower(pathinfo($bannerPath, PATHINFO_EXTENSION));
        $allowedImageExtensions = ['webp', 'jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg'];
        $hasBannerImage = $bannerPath !== ''
            && in_array($bannerExtension, $allowedImageExtensions, true)
            && Storage::disk('public')->exists($bannerPath);

        if (! $hasBannerImage) {
            abort(404);
        }

        $disk = Storage::disk('public');
        $mimeType = $disk->mimeType($bannerPath) ?: 'application/octet-stream';
        $fileContents = $disk->get($bannerPath);
        $eventName = trim(implode(' ', array_filter([
            $event->course?->type,
            $event->course?->name,
        ])));
        $eventNameSlug = Str::slug($eventName);
        $eventDate = $event->eventDates->first()?->date;
        $eventDateFormatted = $eventDate
            ? Carbon::parse((string) $eventDate)->format('d-m-Y')
            : Carbon::today()->format('d-m-Y');
        $downloadFileName = sprintf(
            '%s_%s.%s',
            $eventNameSlug !== '' ? $eventNameSlug : 'evento',
            $eventDateFormatted,
            $bannerExtension !== '' ? $bannerExtension : 'webp',
        );

        return response()->streamDownload(static function () use ($fileContents): void {
            echo $fileContents;
        }, $downloadFileName, ['Content-Type' => $mimeType]);
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

    public function portals(): View
    {
        return view('pages.web.portals.index', [
            'portalCards' => $this->portalCards(),
        ]);
    }

    public function portalShow(Portal $portal): View
    {
        return view('pages.web.portals.show', [
            'portalCard' => $this->portalCards()[$portal->value],
        ]);
    }

    public function portalAccess(
        Request $request,
        Portal $portal,
        UserPortalResolver $userPortalResolver,
        PortalSessionManager $portalSessionManager,
    ): RedirectResponse {
        if (! $request->user()) {
            return redirect()->route('login', ['portal' => $portal->value]);
        }

        $user = $request->user();

        if (! $userPortalResolver->canAccess($user, $portal)) {
            return redirect()->route('app.start');
        }

        $portalSessionManager->remember($request->session(), $portal);

        return redirect()->route($portal->entryRoute());
    }

    private function hasScheduleMatchForAllDays(Training $event): bool
    {
        return DayScheduleHelper::hasAllDaysMatch($event->eventDates, $event->scheduleItems);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function portalCards(): array
    {
        return [
            Portal::Base->value => [
                'key' => Portal::Base->value,
                'label' => Portal::Base->label(),
                'headline' => 'Portal Base e Treinamentos',
                'eyebrow' => 'Portal para igrejas-base e equipes de treinamento',
                'description' => 'Organiza a frente ministerial da base, os treinamentos, materiais, agenda e o acompanhamento da operacao local.',
                'who_uses' => [
                    'Direcao local e coordenacao operacional',
                    'Professores, facilitadores e mentores',
                    'Fieldworkers quando atuam na frente de base',
                ],
                'what_it_solves' => [
                    'Centraliza eventos, agenda, materiais e leitura da base',
                    'Reduz a fragmentacao entre operacao local e treinamentos',
                    'Cria um ponto de entrada claro para quem serve no campo',
                ],
                'cta_label' => 'Entrar no Portal Base',
                'route' => route('web.portals.show', Portal::Base->value),
                'access_route' => route('web.portals.access', Portal::Base->value),
                'tone' => 'sky',
                'icon' => 'squares-2x2',
            ],
            Portal::Staff->value => [
                'key' => Portal::Staff->value,
                'label' => Portal::Staff->label(),
                'headline' => 'Portal Staff / Governanca',
                'eyebrow' => 'Portal para direcao nacional, conselho e acompanhamento institucional',
                'description' => 'Reune governanca, bases acompanhadas, estoque central e a leitura institucional da plataforma ministerial.',
                'who_uses' => [
                    'Direcao nacional e liderancas de staff',
                    'Conselho Nacional e governanca colegiada',
                    'Fieldworkers em leitura institucional das bases',
                ],
                'what_it_solves' => [
                    'Separa governanca institucional da operacao dos eventos',
                    'Consolida acompanhamento de bases, relatorios e estoque central',
                    'Cria uma area propria para Conselho, documentos e deliberacoes',
                ],
                'cta_label' => 'Entrar no Portal Staff',
                'route' => route('web.portals.show', Portal::Staff->value),
                'access_route' => route('web.portals.access', Portal::Staff->value),
                'tone' => 'amber',
                'icon' => 'building-office-2',
            ],
            Portal::Student->value => [
                'key' => Portal::Student->value,
                'label' => Portal::Student->label(),
                'headline' => 'Portal do Aluno',
                'eyebrow' => 'Portal para quem participa dos treinamentos',
                'description' => 'Concentra a jornada do aluno com inscricoes, historico, comprovantes, treinamentos e futuras entregas como certificados.',
                'who_uses' => [
                    'Alunos inscritos nos treinamentos',
                    'Participantes em andamento ou com historico',
                    'Quem precisa acompanhar comprovantes e proximos passos',
                ],
                'what_it_solves' => [
                    'Mostra com clareza onde o aluno acompanha sua jornada',
                    'Agrupa treinamentos, comprovantes e historico em um so lugar',
                    'Melhora a entrada publica para quem chega como participante',
                ],
                'cta_label' => 'Entrar no Portal do Aluno',
                'route' => route('web.portals.show', Portal::Student->value),
                'access_route' => route('web.portals.access', Portal::Student->value),
                'tone' => 'slate',
                'icon' => 'academic-cap',
            ],
        ];
    }
}
