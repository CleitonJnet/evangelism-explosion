<x-layouts.app-simple :title="__('Portais')">
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
                'description' => __('Professor certificado por EE para ministrar Clinicas de EE para outras igrejas'),
                'route' => 'app.teacher.dashboard',
                'gate' => 'access-teacher',
                'visibility' => 'progression',
            ],
            [
                'key' => 'facilitator',
                'label' => __('Teacher/Facilitator'),
                'description' => __('Professor credenciado em uma Clinica para implementacao do EE na igreja local.'),
                'route' => 'app.facilitator.dashboard',
                'gate' => 'access-facilitator',
                'visibility' => 'progression',
            ],
            [
                'key' => 'mentor',
                'label' => __('Mentor'),
                'description' => __('Participante credenciado em Clinica de EE ou membro treinado na igreja local para dirigir equipes de STP.'),
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
        $hasPortals = count($resolvedPortals) > 0;
    @endphp

    <div class="min-h-screen ee-app-bg px-6 py-12">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10">
            <div class="flex flex-col gap-3">
                @if ($displayName !== '')
                    <flux:heading size="lg" level="2">
                        {{ __('Bem-vindo, :name!', ['name' => $displayName]) }}
                    </flux:heading>
                @endif

                <flux:heading size="xl" level="1">{{ __('Escolha o portal de entrada') }}</flux:heading>

                <flux:text class="max-w-3xl text-base text-[color:var(--ee-app-muted)]">
                    {{ __('A seguranca continua sendo definida pelas roles. Aqui voce escolhe apenas o portal de experiencia que deseja usar agora.') }}
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

            @if ($hasPortals)
                <div class="grid gap-6 md:grid-cols-2 xl:grid-cols-3">
                    @foreach ($resolvedPortals as $portal)
                        @php
                            $isSuggested = $portal['key'] === $suggestedPortal?->value;
                            $isCurrent = $portal['key'] === $currentPortal?->value;
                            $isLastUsed = $portal['key'] === $lastPortal?->value;
                        @endphp

                        <div class="flex h-full flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 shadow-sm backdrop-blur"
                            data-test="portal-card-{{ $portal['key'] }}">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex flex-col gap-2">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="rounded-full bg-sky-500/10 px-3 py-1 text-xs font-semibold uppercase tracking-[0.24em] text-sky-700">
                                            {{ $portal['label'] }}
                                        </span>

                                        @if ($isSuggested)
                                            <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs font-semibold text-emerald-700">
                                                {{ __('Sugerido') }}
                                            </span>
                                        @endif

                                        @if ($isCurrent)
                                            <span class="rounded-full bg-amber-500/10 px-3 py-1 text-xs font-semibold text-amber-700">
                                                {{ __('Atual') }}
                                            </span>
                                        @elseif ($isLastUsed)
                                            <span class="rounded-full bg-zinc-500/10 px-3 py-1 text-xs font-semibold text-zinc-600">
                                                {{ __('Ultimo usado') }}
                                            </span>
                                        @endif
                                    </div>

                                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                                        {{ $portal['description'] }}
                                    </flux:text>
                                </div>
                            </div>

                            <div class="flex-1 rounded-2xl border border-dashed border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-bg)]/60 p-4 text-sm text-[color:var(--ee-app-muted)]">
                                {{ __('Portal orientado para a experiencia principal do usuario, preservando a navegacao legada por papel quando necessario.') }}
                            </div>

                            <form method="POST" action="{{ route('app.portal.select', ['portal' => $portal['key']]) }}" class="flex justify-end">
                                @csrf
                                <flux:button variant="primary" type="submit" data-test="portal-access-{{ $portal['key'] }}">
                                    {{ __('Entrar neste portal') }}
                                </flux:button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @else
                <flux:callout variant="warning" icon="shield-exclamation"
                    heading="{{ __('Nenhum portal esta disponivel para este usuario.') }}">
                    <flux:text class="text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Sua conta foi autenticada com sucesso, mas ainda nao possui um portal mapeado. As roles continuam sendo a base da seguranca, entao esse acesso precisa ser liberado no cadastro.') }}
                    </flux:text>
                </flux:callout>
            @endif

            <section class="flex flex-col gap-4 rounded-3xl border border-[color:var(--ee-app-border)] bg-[color:var(--ee-app-surface)] p-6 shadow-sm">
                <div class="flex flex-col gap-2">
                    <flux:heading size="lg" level="2">{{ __('Compatibilidade com a area atual') }}</flux:heading>
                    <flux:text class="max-w-3xl text-sm text-[color:var(--ee-app-muted)]">
                        {{ __('Os acessos legados por papel continuam disponiveis abaixo para manter a compatibilidade enquanto a navegacao por portais evolui.') }}
                    </flux:text>
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
                                    <span class="rounded-full bg-emerald-500/10 px-3 py-1 text-xs text-nowrap font-semibold text-emerald-700">
                                        {{ __('Acesso liberado') }}
                                    </span>
                                @else
                                    <span class="rounded-full bg-zinc-500/10 px-3 py-1 text-xs text-nowrap font-semibold text-zinc-500">
                                        {{ __('Sem acesso') }}
                                    </span>
                                @endcan
                            </div>

                            <div class="flex items-center {{ $canAccess ? 'justify-end' : 'justify-between' }} gap-4">
                                <div class="{{ $canAccess ? 'hidden' : 'flex' }} items-center gap-2 text-xs text-[color:var(--ee-app-muted)]">
                                    <flux:icon.lock-closed variant="outline" class="size-4" />
                                    <span>{{ __('Requer permissao no cadastro') }}</span>
                                </div>

                                @can($role['gate'])
                                    <flux:button variant="outline" :href="route($role['route'])" data-test="role-access-{{ $role['key'] }}">
                                        {{ __('Acessar legado') }}
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
            </section>
        </div>
    </div>
</x-layouts.app-simple>
