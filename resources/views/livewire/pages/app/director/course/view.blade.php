@php
    use App\Helpers\MoneyHelper;
    use Illuminate\Support\Facades\Storage;

    $resolveAssetUrl = static function (?string $asset): ?string {
        $assetValue = trim((string) $asset);

        if ($assetValue === '') {
            return null;
        }

        if (str_starts_with($assetValue, 'http')) {
            return $assetValue;
        }

        $normalizedAsset = ltrim($assetValue, '/');

        return Storage::disk('public')->exists($normalizedAsset)
            ? Storage::disk('public')->url($normalizedAsset)
            : null;
    };

    $badgeTextColor = static function (?string $hexColor): string {
        $color = trim((string) $hexColor);

        if (!preg_match('/^#([A-Fa-f0-9]{3}|[A-Fa-f0-9]{6})$/', $color)) {
            return '#0f172a';
        }

        $normalized = ltrim($color, '#');

        if (strlen($normalized) === 3) {
            $normalized = collect(str_split($normalized))->map(fn(string $char): string => $char . $char)->implode('');
        }

        $red = hexdec(substr($normalized, 0, 2));
        $green = hexdec(substr($normalized, 2, 2));
        $blue = hexdec(substr($normalized, 4, 2));
        $luminance = ($red * 299 + $green * 587 + $blue * 114) / 1000;

        return $luminance >= 160 ? '#0f172a' : '#f8fafc';
    };

    $formatExecution = static fn(?int $execution): string => match ((int) $execution) {
        1 => __('Implementação local'),
        default => __('Liderança'),
    };

    $formatCurrency = static fn($value): string => MoneyHelper::format_money($value) ?: __('Não informado');
    $teacherLabel = (int) $course->execution === 1 ? __('Facilitador') : __('Professor');
    $teachersLabel = (int) $course->execution === 1 ? __('Facilitadores do curso') : __('Professores do curso');

    $logoUrl = $resolveAssetUrl($course->logo);
    $bannerUrl = $resolveAssetUrl($course->banner) ?: asset('images/cover.webp');
@endphp

<div class="space-y-6">
    <section
        class="rounded-2xl border border-amber-300/20 bg-linear-to-br from-slate-100 via-white to-slate-200 p-4 shadow-lg sm:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4 border-b-2 border-slate-200/80 pb-4">
            <div class="flex flex-1 items-start gap-4">
                <div
                    class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm h-60 aspect-21/9 relative">
                    <img src="{{ $bannerUrl }}" alt="{{ __('Banner do curso') }}" class="h-full w-full object-cover">

                    {{-- Logo do curso --}}
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ __('Logo do curso') }}"
                            class="h-20 w-20 rounded-2xl backdrop-blur object-contain shadow-sm absolute right-2 top-1">
                    @else
                        <div class="inline-flex h-28 min-w-32 items-center justify-center rounded-2xl text-lg font-bold shadow-sm absolute right-2 top-1"
                            style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $badgeTextColor($course->color) }}">
                            {{ str($course->initials ?: $course->name)->limit(4, '') ?: 'CUR' }}
                        </div>
                    @endif
                </div>

                <div class="flex items-start gap-4">

                    <div class="space-y-2 pt-1">
                        @if ($course->type)
                            <span class="rounded-full bg-white/80 py-1 text-sm font-bold uppercase text-slate-700">
                                {{ $course->type }}
                            </span>
                        @endif


                        <h2 class="text-2xl font-semibold text-slate-900" style="font-family: 'Cinzel', serif;">
                            {{ $course->name ?: __('Curso sem nome cadastrado') }} -
                            {{ $course->initials ?: __('Sem sigla cadastrada') }}
                        </h2>

                        <p class="max-w-3xl text-sm leading-6 text-slate-600">
                            {{ $course->slogan ?: __('Este curso ainda não possui apresentação resumida cadastrada.') }}
                        </p>
                        <span class="rounded-full px-3 py-1 text-xs font-light tracking-wide"
                            style="background-color: {{ $course->color ?: '#e2e8f0' }}; color: {{ $badgeTextColor($course->color) }}">
                            {{ $formatExecution($course->execution) }}
                        </span>

                    </div>
                </div>
            </div>

            <div class="flex flex-col items-end gap-3">
                <div
                    class="flex items-center gap-3 rounded-full bg-white/80 px-4 py-2 text-sm font-semibold text-slate-800">
                    <span class="h-3.5 w-3.5 rounded-full border border-slate-300"
                        style="background-color: {{ $course->color ?: '#e2e8f0' }}"></span>
                    <span>{{ __('Cor temática: :color', ['color' => $course->color ?: __('Não informada')]) }}</span>
                </div>

                <div class="rounded-2xl border border-slate-200 bg-white/80 px-4 py-3 text-sm text-slate-700">
                    <div class="font-semibold text-slate-900">{{ __('Ministério vinculado') }}</div>
                    <div>{{ $course->ministry?->name ?: __('Não informado') }}</div>
                </div>
            </div>
        </div>
    </section>

    <div class="flex flex-wrap gap-3">
        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Unidades') }}</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $sections->total() }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Professores') }}</div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $teachersCount }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Professores ativos') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $activeTeachersCount }}</div>
        </div>

        <div class="min-w-40 flex-1 rounded-xl border border-slate-200 bg-white p-4">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Sessões mínimas STP') }}
            </div>
            <div class="mt-2 text-2xl font-bold text-slate-900">{{ $course->min_stp_sessions ?: 0 }}</div>
        </div>
    </div>

    <div class="grid gap-6 lg:grid-cols-[1.2fr_0.8fr]">
        <section class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm sm:p-5">
            <div class="border-b border-slate-200 pb-3">
                <h3 class="text-lg font-semibold text-slate-900">{{ __('Informações do curso') }}</h3>
                <p class="text-sm text-slate-600">
                    {{ __('Consulte a identificação, classificação e dados de apresentação do curso selecionado.') }}
                </p>
            </div>

            <div class="mt-5 grid gap-4 sm:grid-cols-2">
                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Sessões mínimas STP') }}</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $course->min_stp_sessions ?: 0 }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                        {{ __('Preço sugerido') }}</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $formatCurrency($course->price) }}</div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Saiba mais') }}
                    </div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">
                        @if ($course->learnMoreLink)
                            <a href="{{ $course->learnMoreLink }}" target="_blank" rel="noreferrer"
                                class="break-all text-amber-800 underline decoration-amber-300 underline-offset-4">
                                {{ $course->learnMoreLink }}
                            </a>
                        @else
                            {{ __('Não informado') }}
                        @endif
                    </div>
                </div>

                <div class="rounded-xl border border-slate-200 bg-slate-50 p-4 sm:col-span-2">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Slogan') }}</div>
                    <div class="mt-2 text-sm font-semibold text-slate-900">{{ $course->slogan ?: __('Não informado') }}
                    </div>
                </div>
            </div>
        </section>

        <section class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm sm:p-5">
            <div class="flex flex-wrap items-start justify-between gap-4 border-b border-slate-200 pb-3">
                <div>
                    <h3 class="text-lg font-semibold text-slate-900">{{ __('Material de estudos do aluno') }}</h3>
                    <p class="text-sm text-slate-600">
                        {{ __('Esses itens definem o kit do aluno e alimentam o fluxo de entrega física no treinamento.') }}
                    </p>
                </div>

                <flux:button variant="primary" wire:click="openStudyMaterialsModal">
                    {{ __('Gerenciar materiais') }}
                </flux:button>
            </div>

            @if ($studyMaterials->isEmpty())
                <div class="mt-5 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600">
                    {{ __('Nenhum material de estudo foi definido para este curso ainda.') }}
                </div>
            @else
                <div class="mt-5 grid gap-4">
                    @foreach ($studyMaterials as $material)
                        <article class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-3">
                                <div>
                                    <div class="text-sm font-semibold text-slate-900">{{ $material->name }}</div>
                                    <div class="mt-1 text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ $material->isComposite() ? __('Composto') : __('Simples') }}
                                        @if (! $material->is_active)
                                            · {{ __('Inativo') }}
                                        @endif
                                    </div>
                                </div>

                                @if ($material->minimum_stock !== null)
                                    <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                        {{ __('Estoque mínimo') }}: {{ $material->minimum_stock }}
                                    </span>
                                @endif
                            </div>

                            @if (filled($material->description))
                                <p class="mt-3 text-sm leading-6 text-slate-600">
                                    {{ $material->description }}
                                </p>
                            @endif

                            @if ($material->isComposite() && $material->components->isNotEmpty())
                                <div class="mt-4 border-t border-slate-200 pt-4">
                                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">
                                        {{ __('Itens incluídos no kit') }}
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        @foreach ($material->components as $component)
                                            <span class="rounded-full bg-white px-3 py-1 text-xs font-semibold text-slate-700">
                                                {{ $component->componentMaterial?->name ?? __('Componente removido') }}
                                                x{{ $component->quantity }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </article>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    <section class="rounded-2xl border border-slate-200 bg-white/95 p-4 shadow-sm sm:p-5">
        <div class="border-b border-slate-200 pb-3">
            <h3 class="text-lg font-semibold text-slate-900">{{ __('Conteúdo pedagógico') }}</h3>
            <p class="text-sm text-slate-600">
                {{ __('Acompanhe os textos descritivos e o conhecimento-base associado ao curso.') }}
            </p>
        </div>

        <div class="mt-5 grid gap-4 lg:grid-cols-2">
            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Público-alvo') }}
                </div>
                <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $course->targetAudience ?: __('Não informado') }}
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Descrição') }}</div>
                <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $course->description ?: __('Não informada') }}
                </div>
            </div>

            <div class="rounded-xl border border-slate-200 bg-slate-50 p-4">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('Know how') }}</div>
                <div class="mt-3 whitespace-pre-line text-sm leading-6 text-slate-700">
                    {{ $course->knowhow ?: __('Não informado') }}
                </div>
            </div>
        </div>
    </section>

        <section class="rounded-2xl border border-slate-200 bg-white/95 p-6 shadow-sm">
            <div class="flex items-center justify-between gap-4">
                <div>
                    <flux:heading size="sm" level="2">
                        {{ $teachersLabel }} ({{ $teachersCount }})
                    </flux:heading>
                    <flux:text class="text-sm text-slate-600">
                        {{ __('Lista de :label responsáveis por ministrar o ensino das aulas em grupo deste curso.', ['label' => mb_strtolower($teachersLabel)]) }}
                    </flux:text>
                </div>
                <flux:button variant="primary" wire:click="openCreateTeacherModal">
                    {{ __('Adicionar') }} {{ mb_strtolower($teacherLabel) }}
                </flux:button>
            </div>

            <div class="mt-6 overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <colgroup>
                        <col class="w-20">
                        <col>
                        <col>
                        <col>
                        <col class="w-28">
                        <col class="w-16">
                    </colgroup>
                    <thead class="bg-slate-50 text-xs uppercase text-slate-600">
                        <tr class="border-b border-slate-200">
                            <th class="px-3 py-2 text-center">{{ __('Foto') }}</th>
                            <th class="px-3 py-2">{{ $teacherLabel }}</th>
                            <th class="px-3 py-2">{{ __('Igreja') }}</th>
                            <th class="px-3 py-2">{{ __('Cidade / Estado') }}</th>
                            <th class="px-3 py-2 text-center">{{ __('Status') }}</th>
                            <th class="px-3 py-2 w-16 text-right">{{ __('Ações') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200">
                        @forelse ($teachers as $teacher)
                            <tr wire:key="teacher-{{ $teacher->id }}"
                                class="odd:bg-white even:bg-slate-50/80">
                                <td class="px-3 py-2">
                                    <div class="flex justify-center">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full bg-slate-500 text-xs font-semibold text-slate-50 ring-1 ring-black/10">
                                            @if ($teacher->profile_photo_url)
                                                <img src="{{ $teacher->profile_photo_url }}"
                                                    alt="{{ __('Foto de perfil de :name', ['name' => $teacher->name]) }}"
                                                    class="h-full w-full object-cover">
                                            @else
                                                {{ $teacher->initials() }}
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="px-3 py-2">
                                    @if ($teacher->church_id)
                                        <a class="font-semibold text-slate-900"
                                            href="{{ route('app.director.church.profile.show', ['church' => $teacher->church_id, 'profile' => $teacher->id]) }}">
                                            {{ $teacher->name }}
                                        </a>
                                    @else
                                        <span class="font-semibold text-slate-900">{{ $teacher->name }}</span>
                                    @endif
                                    <div class="text-xs text-slate-500">{{ $teacher->email }}</div>
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    @if ($teacher->church)
                                        <div class="font-semibold text-slate-900">{{ $teacher->church->name }}</div>
                                        <div class="text-xs text-slate-500">
                                            {{ $teacher->church->pastor ?: __('Pastor não informado') }}
                                        </div>
                                    @else
                                        <span class="text-sm text-slate-500">{{ __('Sem igreja vinculada') }}</span>
                                    @endif
                                </td>
                                <td class="px-3 py-2 text-slate-700">
                                    @php
                                        $teacherLocation = implode(' / ', array_filter([$teacher->city, $teacher->state]));
                                    @endphp
                                    {{ $teacherLocation !== '' ? $teacherLocation : __('Não informado') }}
                                </td>
                                <td class="px-3 py-2 text-center text-slate-700">
                                    <x-app.switch-schedule :label="(int) ($teacher->pivot->status ?? 0) === 1 ? __('Ativo') : __('Inativo')"
                                        :key="'course-teacher-status-' . $teacher->id" :checked="(int) ($teacher->pivot->status ?? 0) === 1"
                                        wrapperClass="bg-red-50 hover:bg-red-100 border-red-200 [&:has(input:checked)]:bg-sky-50 [&:has(input:checked)]:hover:bg-sky-100 [&:has(input:checked)]:border-sky-200"
                                        trackClass="bg-red-300 peer-checked:bg-sky-950 border-red-400/70 peer-checked:border-slate-400/70"
                                        wire:change="toggleTeacherStatus({{ $teacher->id }}, $event.target.checked)" />
                                </td>
                                <td class="px-3 py-2 w-16">
                                    <div class="flex items-center justify-end gap-2 whitespace-nowrap">
                                        <flux:button class="shrink-0 px-2" size="sm" :square="false"
                                            variant="danger" icon="trash" icon:variant="outline"
                                            aria-label="{{ __('Remover') }}"
                                            wire:click="openDeleteTeacherModal({{ $teacher->id }})" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-3 py-4 text-sm text-slate-600" colspan="6">
                                    {{ __('Nenhum professor associado.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

    <flux:modal name="teacher-modal" wire:model="showTeacherModal" class="max-w-xl w-full bg-sky-950! p-0!">
        <form wire:submit="saveTeacher">
            <div class="flex max-h-[90vh] flex-col overflow-hidden rounded-2xl">
                <header class="sticky top-0 z-20 border-b border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                    <h3 class="text-lg font-semibold">{{ __('Adicionar') . ' ' . mb_strtolower($teacherLabel) }}</h3>
                    <p class="text-sm opacity-90">
                        {{ __('Associe novos responsáveis ao curso e defina o status inicial.') }}
                    </p>
                </header>

                <div class="min-h-0 flex-1 overflow-y-auto bg-white px-6 py-6">
                    <div class="space-y-6">
                        <p class="text-sm text-slate-600">
                            {{ __('Para adicionar :label, primeiro atribua a função Professor ao usuário no setup do sistema.', ['label' => mb_strtolower($teachersLabel)]) }}
                        </p>

                        @if ($teacherAlreadyAssignedWarning)
                            <flux:callout variant="warning" icon="exclamation-triangle"
                                heading="{{ __('Não foi possível salvar o registro.') }}">
                                {{ __('Este registro já está na lista deste curso.') }}
                            </flux:callout>
                        @endif

                        <div class="grid gap-4">
                            <flux:input wire:model.live.debounce.300ms="teacherSearch" :label="__('Buscar') . ' ' . mb_strtolower($teacherLabel)"
                                :placeholder="__('Digite o nome ou e-mail')" autofocus />
                            <flux:select wire:model="teacherForm.user_id" :label="$teacherLabel"
                                :placeholder="__('Selecione') . ' ' . mb_strtolower($teacherLabel)">
                                @forelse ($teacherCandidates as $teacher)
                                    @php
                                        $isAssigned = in_array($teacher->id, $assignedTeacherIds, true);
                                    @endphp
                                    <option value="{{ $teacher->id }}" @if ($isAssigned) disabled @endif>
                                        {{ $teacher->name }} ({{ $teacher->email }})
                                        @if ($isAssigned)
                                            - {{ __('Já selecionado') }}
                                        @endif
                                    </option>
                                @empty
                                    <option value="" disabled>{{ __('Nenhum professor disponível.') }}</option>
                                @endforelse
                            </flux:select>
                            <flux:text class="text-xs text-slate-500">
                                {{ __('Mostrando até 15 resultados.') }}
                            </flux:text>
                            <flux:text class="text-xs text-slate-500">
                                {{ __('Registros já associados aparecem desabilitados.') }}
                            </flux:text>
                            @php
                                $selectedTeacherId = $teacherForm['user_id'] ?? null;
                                $selectedAlreadyAssigned =
                                    $selectedTeacherId && in_array($selectedTeacherId, $assignedTeacherIds, true);
                            @endphp
                            @if ($selectedAlreadyAssigned)
                                <flux:text class="text-sm text-amber-700">
                                    {{ __('Este registro já está na lista deste curso.') }}
                                </flux:text>
                            @endif

                            <flux:select wire:model="teacherForm.status" :label="__('Status')">
                                <option value="1">{{ __('Ativo') }}</option>
                                <option value="0">{{ __('Inativo') }}</option>
                            </flux:select>

                            @error('teacherForm.user_id')
                                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                            @enderror
                            @error('teacherForm.status')
                                <flux:text class="text-sm text-red-600">{{ $message }}</flux:text>
                            @enderror
                        </div>
                    </div>
                </div>

                <footer class="sticky bottom-0 z-20 border-t border-sky-800 bg-sky-950 px-6 py-4 text-sky-50">
                    <div class="flex justify-between gap-3">
                        @php
                            $selectedTeacherId = $teacherForm['user_id'] ?? null;
                            $selectedAlreadyAssigned =
                                $selectedTeacherId && in_array($selectedTeacherId, $assignedTeacherIds, true);
                        @endphp
                        <x-src.btn-silver type="button" wire:click="closeTeacherModal" wire:loading.attr="disabled"
                            wire:target="saveTeacher">
                            {{ __('Cancelar') }}
                        </x-src.btn-silver>
                        <x-src.btn-gold type="submit" wire:loading.attr="disabled" wire:target="saveTeacher"
                            :disabled="$selectedAlreadyAssigned">
                            {{ __('Salvar') }}
                        </x-src.btn-gold>
                    </div>
                </footer>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="delete-teacher-modal" wire:model="showDeleteTeacherModal" class="max-w-md">
        <div class="space-y-6">
            <div>
                <flux:heading size="lg">{{ __('Remover professor') }}</flux:heading>
                <flux:subheading>
                    {{ __('Esta ação é permanente. Deseja continuar?') }}
                </flux:subheading>
            </div>

            <div class="flex flex-wrap justify-end gap-2">
                <flux:button variant="ghost" wire:click="closeDeleteTeacherModal">
                    {{ __('Cancelar') }}
                </flux:button>
                <flux:button variant="danger" wire:click="confirmDeleteTeacher">
                    {{ __('Remover') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="director-course-study-materials-modal" wire:model="showStudyMaterialsModal" class="max-w-4xl">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Materiais de estudo do curso') }}</flux:heading>
                <flux:subheading>
                    {{ __('Selecione os itens que compõem o material de estudos e o kit do aluno deste curso.') }}
                </flux:subheading>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($studyMaterialOptions as $materialOption)
                    <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                        <input type="checkbox" value="{{ $materialOption['value'] }}" wire:model.live="selectedStudyMaterialIds"
                            class="mt-1 rounded border-slate-300">
                        <div class="space-y-1">
                            <div class="font-semibold text-slate-900">{{ $materialOption['label'] }}</div>
                            <div class="text-xs uppercase tracking-wide text-slate-500">
                                {{ $materialOption['description'] }}
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 text-sm text-slate-600 md:col-span-2">
                        {{ __('Cadastre materiais no estoque para vinculá-los ao curso.') }}
                    </div>
                @endforelse
            </div>

            @error('selectedStudyMaterialIds.*')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    {{ $message }}
                </div>
            @enderror

            <div class="flex items-center justify-end gap-3">
                <x-src.btn-silver type="button" wire:click="closeStudyMaterialsModal">
                    {{ __('Cancelar') }}
                </x-src.btn-silver>
                <flux:button variant="primary" wire:click="saveStudyMaterials">
                    {{ __('Salvar materiais') }}
                </flux:button>
            </div>
        </div>
    </flux:modal>
</div>
