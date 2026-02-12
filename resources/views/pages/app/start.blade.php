<x-layouts.app-simple :title="__('Triagem')">
    @php
        $rawName = trim((string) (auth()->user()->name ?? ''));
        $nameParts = preg_split('/\s+/', $rawName, -1, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $nameParts[0] ?? '';
        $prepositions = ['de', 'da', 'do', 'das', 'dos'];
        $lastNameParts = [];

        if (!empty($nameParts)) {
            $lastNameParts[] = array_pop($nameParts);

            while (!empty($nameParts)) {
                $candidate = end($nameParts);

                if (!in_array(mb_strtolower($candidate), $prepositions, true)) {
                    break;
                }

                $lastNameParts[] = array_pop($nameParts);
            }
        }

        $lastName = implode(' ', array_reverse($lastNameParts));
        $displayName = trim(implode(' ', array_filter([$firstName, $lastName])));

        $roles = [
            [
                'key' => 'board',
                'label' => __('Board Member'),
                'description' => __('Conselho nacional e decisoes estrategicas.'),
                'route' => 'app.board.dashboard',
                'gate' => 'access-board',
                'visibility' => 'staff',
            ],
            [
                'key' => 'director',
                'label' => __('National Director'),
                'description' => __('Gestao geral, igrejas e treinamentos.'),
                'route' => 'app.director.dashboard',
                'gate' => 'access-director',
                'visibility' => 'staff',
            ],
            [
                'key' => 'fieldworker',
                'label' => __('Field Worker'),
                'description' => __('Missionarios em campo apoiando igrejas.'),
                'route' => 'app.fieldworker.dashboard',
                'gate' => 'access-fieldworker',
                'visibility' => 'staff',
            ],
            [
                'key' => 'teacher',
                'label' => __('Teacher/EE-certified'),
                'description' => __('Professor certificado por EE para ministrar Clínicas de EE para outras igrejas'),
                'route' => 'app.teacher.dashboard',
                'gate' => 'access-teacher',
                'visibility' => 'progression',
            ],
            [
                'key' => 'facilitator',
                'label' => __('Teacher/Facilitator'),
                'description' => __('Professor credenciado em uma Clínica para implementação do EE na igreja local.'),
                'route' => 'app.facilitator.dashboard',
                'gate' => 'access-facilitator',
                'visibility' => 'progression',
            ],
            [
                'key' => 'mentor',
                'label' => __('Mentor'),
                'description' => __(
                    'Participante Cresdenciado em Clínica de EE ou membro treinado na igreja local para dirigir equipes de STP.',
                ),
                'route' => 'app.mentor.dashboard',
                'gate' => 'access-mentor',
                'visibility' => 'progression',
            ],
            [
                'key' => 'student',
                'label' => __('Student'),
                'description' => __('Painel do aluno e materiais de estudo.'),
                'route' => 'app.student.dashboard',
                'gate' => 'access-student',
                'visibility' => 'progression',
            ],
        ];

        $hasAnyRole = auth()->user()->roles()->exists();
    @endphp

    <div class="min-h-screen ee-app-bg px-6 py-12">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10">
            <div class="flex flex-col gap-3">
                @if ($displayName !== '')
                    <flux:heading size="lg" level="2">
                        {{ __('Bem-vindo, :name!', ['name' => $displayName]) }}
                    </flux:heading>
                @endif
                <flux:heading size="xl" level="1">{{ __('Escolha como deseja acessar') }}</flux:heading>
                <flux:text class="max-w-2xl text-base text-[color:var(--ee-app-muted)]">
                    {{ __('Selecione o perfil que voce deseja usar agora. Apenas perfis autorizados ficam liberados.') }}
                </flux:text>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <flux:button variant="outline" :href="route('web.home')" data-test="back-to-site">
                    {{ __('Voltar para o site') }}
                </flux:button>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:button variant="outline" type="submit" data-test="logout">
                        {{ __('Sair') }}
                    </flux:button>
                </form>
            </div>

            @if (!$hasAnyRole)
                <flux:callout variant="warning" icon="exclamation-triangle"
                    heading="{{ __('Seu usuario ainda nao possui um perfil habilitado.') }}" />
            @endif

            <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                @foreach ($roles as $role)
                    @php
                        $canAccess = auth()->user()->can($role['gate']);
                        $isProgression = $role['visibility'] === 'progression';
                        $shouldShow = $isProgression || $canAccess;
                    @endphp

                    @if (!$shouldShow)
                        @continue
                    @endif

                    <div class="flex h-full flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 shadow-sm backdrop-blur {{ $canAccess ? '' : 'opacity-75' }}"
                        data-test="role-card-{{ $role['key'] }}">
                        <div class="flex items-start justify-between gap-4">
                            <div class="flex flex-col gap-1">
                                <flux:heading size="base" level="2">{{ $role['label'] }}</flux:heading>
                                <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                                    {{ $role['description'] }}
                                </flux:text>
                            </div>
                            @can($role['gate'])
                                <span
                                    class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs text-nowrap font-semibold text-emerald-700 dark:text-emerald-300">
                                    {{ __('Acesso liberado') }}
                                </span>
                            @else
                                <span
                                    class="rounded-full bg-zinc-500/10 px-3 py-1 text-xs text-nowrap font-semibold text-zinc-500 dark:text-white/50">
                                    {{ __('Sem acesso') }}
                                </span>
                            @endcan
                        </div>

                        <div class="flex items-center {{ $canAccess ? 'justify-end' : 'justify-between' }} gap-4">
                            <div
                                class="{{ $canAccess ? 'hidden' : 'flex' }} items-center gap-2 text-xs text-[color:var(--ee-app-muted)]">
                                <flux:icon.lock-closed variant="outline" class="size-4" />
                                <span>{{ __('Requer permissao no cadastro') }}</span>
                            </div>

                            @can($role['gate'])
                                <flux:button variant="primary" :href="route($role['route'])"
                                    data-test="role-access-{{ $role['key'] }}">
                                    {{ __('Acessar') }}
                                </flux:button>
                            @else
                                <flux:button variant="outline" disabled>
                                    {{ __('Sem acesso') }}
                                </flux:button>
                            @endcan
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</x-layouts.app-simple>
