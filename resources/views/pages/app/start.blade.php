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

    <div class="min-h-screen ee-app-bg px-3 py-8 sm:px-4 sm:py-10 md:px-5 md:py-12 lg:px-6">
        <div class="mx-auto flex w-full max-w-6xl flex-col gap-10">
            <section
                class="relative overflow-hidden rounded-3xl bg-sky-950 px-6 py-8 text-slate-100 shadow-xl sm:px-8 lg:px-10 lg:py-10">
                <div class="pointer-events-none absolute -top-[18%] -bottom-[2%] left-0 z-0">
                    <img src="{{ asset('images/logo/ee-white.webp') }}" alt=""
                        class="h-[120%] w-auto max-w-none object-contain opacity-5" aria-hidden="true">
                </div>

                <div class="relative z-10 flex flex-col gap-8 lg:flex-row lg:items-start lg:justify-between">
                    <div class="max-w-3xl space-y-4">
                        <p class="text-sm font-semibold uppercase tracking-[0.24em] text-sky-200/80">
                            {{ __('Plataforma de Gerenciamento Ministerial') }}
                        </p>

                        <div class="space-y-3">
                            @if ($displayName !== '')
                                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                    {{ __('Bem-vindo,') }}
                                    <span
                                        class="bg-linear-to-r from-[#d7b34d] via-[#f4dd8a] to-[#fff4c2] bg-clip-text text-transparent [text-shadow:0_1px_10px_rgba(244,221,138,0.18)]">
                                        {{ $displayName }}
                                    </span>
                                </h1>
                            @else
                                <h1 class="text-3xl font-semibold tracking-tight text-white sm:text-4xl">
                                    {{ __('Bem-vindo à plataforma') }}
                                </h1>
                            @endif

                            <p class="max-w-2xl text-base leading-7 text-sky-100/85">
                                {{ __('Escolha abaixo como deseja acessar o sistema.') }}
                            </p>
                        </div>
                    </div>

                    <div class="flex w-full flex-col gap-3 lg:w-auto lg:min-w-56">
                        <x-src.btn-silver :route="route('web.home')" data-test="back-to-site">
                            {{ __('Voltar para o site') }}
                        </x-src.btn-silver>

                        <form method="POST" action="{{ route('logout') }}" class="w-full">
                            @csrf
                            <x-src.btn-silver type="submit" class="w-full justify-center" data-test="logout">
                                {{ __('Sair') }}
                            </x-src.btn-silver>
                        </form>
                    </div>
                </div>
            </section>

            @if (!$hasAnyRole)
                <flux:callout variant="warning" icon="exclamation-triangle"
                    heading="{{ __('Seu usuario ainda não possui um perfil habilitado.') }}" />
            @endif

            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @foreach ($roles as $role)
                    @php
                        $canAccess = auth()->user()->can($role['gate']);
                    @endphp

                    @if (!$canAccess)
                        @continue
                    @endif

                    <a href="{{ route($role['route']) }}"
                        class="group flex h-full flex-col gap-6 rounded-2xl border border-[color:var(--ee-app-border)] bg-linear-to-br from-[color:var(--ee-app-surface)] via-[color:var(--ee-app-surface)] to-sky-50/70 p-6 shadow-sm backdrop-blur transition duration-200 hover:border-sky-300 hover:from-sky-50 hover:via-white hover:to-amber-50 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-sky-300"
                        data-test="role-card-{{ $role['key'] }}">
                        <div class="flex flex-1 flex-col gap-3">
                            <flux:heading size="lg" level="2"
                                class="font-extrabold leading-tight text-slate-900 transition-colors group-hover:text-sky-900"
                                style="font-family: 'Cinzel', serif;">
                                {{ $role['label'] }}
                            </flux:heading>

                            <div class="h-0.75 w-full bg-linear-to-r from-[#f1d57a] via-[#c7a840] to-[#8a7424]">
                            </div>

                            <flux:text class="text-xs text-[color:var(--ee-app-muted)]">
                                {{ $role['description'] }}
                            </flux:text>
                        </div>

                        <div
                            class="mt-auto flex items-center justify-end gap-4 pt-2 text-sm font-semibold text-sky-800 transition group-hover:text-sky-900">
                            <span>{{ __('Entrar') }}</span>
                            <flux:icon.arrow-right variant="mini" class="size-4" />
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-4">
                <div class="h-px w-full bg-linear-to-r from-transparent via-amber-400/35 to-transparent"></div>
                <p class="pt-6 text-center text-xs text-slate-500">
                    © {{ date('Y') }} — {{ __('Evangelismo Explosivo Internacional no Brasil') }} ::
                    {{ __('Todos os direitos reservados.') }}
                </p>
            </div>

        </div>
    </div>
</x-layouts.app-simple>
