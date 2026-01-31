@props(['title' => null, 'description' => null, 'justify' => 'justify-start', 'class' => ''])

@php
    $route = request()->route();
    $routeName = $route?->getName();

    if (!$routeName || str_starts_with($routeName, 'livewire.')) {
        $referer = request()->headers->get('referer');

        if (is_string($referer) && $referer !== '') {
            try {
                $router = app('router');
                $refererRequest = \Illuminate\Http\Request::create($referer, 'GET');
                $route = $router->getRoutes()->match($refererRequest);
                $router->substituteBindings($route);
                $router->substituteImplicitBindings($route);
                $routeName = $route->getName();
            } catch (\Throwable $exception) {
                $route = request()->route();
                $routeName = $route?->getName();
            }
        }
    }
    $breadcrumbItems = [];
    $parameterLabels = [];
    $actionLabels = [
        'index' => __('Lista'),
        'show' => __('Detalhes'),
        'create' => __('Criar'),
        'edit' => __('Editar'),
        'schedule' => __('Programação'),
    ];

    if ($route && $routeName) {
        $resolveLabel = function ($model): ?string {
            foreach (['name', 'title', 'label', 'display_name'] as $key) {
                $value = data_get($model, $key);

                if (is_string($value) && $value !== '') {
                    return $value;
                }
            }

            foreach (
                ['training', 'course', 'event', 'user', 'church', 'ministry', 'section', 'teacher', 'mentor', 'student']
                as $relation
            ) {
                if (!method_exists($model, $relation)) {
                    continue;
                }

                $related = $model->{$relation};

                if (!$related instanceof \Illuminate\Database\Eloquent\Model) {
                    continue;
                }

                foreach (['name', 'title', 'label', 'display_name'] as $key) {
                    $value = data_get($related, $key);

                    if (is_string($value) && $value !== '') {
                        return $value;
                    }
                }
            }

            $courseName = data_get($model, 'course.name');

            if (is_string($courseName) && $courseName !== '') {
                return $courseName;
            }

            $routeKey = $model->getRouteKey();

            return class_basename($model) . ' #' . $routeKey;
        };

        foreach ($route->parameters() as $param) {
            if (!$param instanceof \Illuminate\Database\Eloquent\Model) {
                continue;
            }

            $label = $resolveLabel($param);

            if ($label === null) {
                continue;
            }

            $parameterLabels[(string) $param->getRouteKey()] = $label;
        }
    }

    if ($routeName && str_starts_with($routeName, 'app.')) {
        $segments = explode('.', $routeName);
        $role = $segments[1] ?? null;
        $resourceSegments = array_slice($segments, 2);
        $actionSegment = null;
        $lastShowUrl = null;

        if ($resourceSegments !== []) {
            $lastSegment = $resourceSegments[array_key_last($resourceSegments)];

            if (array_key_exists($lastSegment, $actionLabels)) {
                $actionSegment = $lastSegment;
                array_pop($resourceSegments);
            }
        }

        if ($role) {
            $roleRoute = 'app.' . $role . '.dashboard';

            $breadcrumbItems[] = [
                'label' => \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $role)),
                'url' => \Illuminate\Support\Facades\Route::has($roleRoute) ? route($roleRoute) : null,
                'current' => false,
            ];
        }

        $baseSegments = array_filter(['app', $role]);

        foreach ($resourceSegments as $segment) {
            $baseSegments[] = $segment;
            $label = \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $segment));
            $singular = \Illuminate\Support\Str::singular($segment);

            $indexRoute = implode('.', $baseSegments) . '.index';
            $singularSegments = $baseSegments;
            $singularSegments[array_key_last($singularSegments)] = $singular;
            $indexRouteSingular = implode('.', $singularSegments) . '.index';

            $indexUrl = null;
            if (\Illuminate\Support\Facades\Route::has($indexRoute)) {
                $indexUrl = route($indexRoute);
            } elseif (\Illuminate\Support\Facades\Route::has($indexRouteSingular)) {
                $indexUrl = route($indexRouteSingular);
            }

            $breadcrumbItems[] = [
                'label' => $label,
                'url' => $indexUrl,
                'current' => false,
            ];

            $param = $route?->parameter($singular);
            $paramValue = null;
            $modelLabel = null;

            if ($param instanceof \Illuminate\Database\Eloquent\Model) {
                $paramValue = $param;
                $modelLabel = $parameterLabels[(string) $param->getRouteKey()] ?? $label;
            } elseif (is_string($param) || is_int($param)) {
                $paramValue = $param;
            }

            $showRoute = implode('.', $baseSegments) . '.show';
            $showRouteSingular = implode('.', $singularSegments) . '.show';
            $showUrl = null;

            if ($paramValue !== null) {
                if (\Illuminate\Support\Facades\Route::has($showRoute)) {
                    $showUrl = route($showRoute, [$singular => $paramValue]);
                } elseif (\Illuminate\Support\Facades\Route::has($showRouteSingular)) {
                    $showUrl = route($showRouteSingular, [$singular => $paramValue]);
                }
            }

            $lastShowUrl = $showUrl;

            if ($modelLabel !== null) {
                $breadcrumbItems[] = [
                    'label' => $modelLabel,
                    'url' => $showUrl,
                    'current' => false,
                ];
            }
        }

        if ($actionSegment === 'edit' && $lastShowUrl) {
            $breadcrumbItems[] = [
                'label' => __('Detalhes'),
                'url' => $lastShowUrl,
                'current' => false,
            ];
        }

        if ($actionSegment) {
            $breadcrumbItems[] = [
                'label' =>
                    $actionLabels[$actionSegment] ??
                    \Illuminate\Support\Str::title(str_replace(['-', '_'], ' ', $actionSegment)),
                'url' => null,
                'current' => true,
            ];
        } elseif ($breadcrumbItems !== []) {
            $breadcrumbItems[array_key_last($breadcrumbItems)]['current'] = true;
        }
    }
@endphp

<section
    {{ $attributes->merge(['class' => 'rounded-2xl border border-amber-300/20 bg-linear-to-br from-white via-slate-50 to-slate-200 px-5 py-2 shadow-sm mb-6 w-full']) }}>
    @if ($breadcrumbItems !== [])
        <nav class="flex flex-wrap items-center gap-2 text-xs py-2 text-amber-700/50" aria-label="{{ __('Breadcrumb') }}">
            @foreach ($breadcrumbItems as $item)
                @if (!$loop->first)
                    <span class="text-[10px] text-amber-700/50">/</span>
                @endif
                @if ($item['current'])
                    <span class="font-semibold text-amber-700" aria-current="page">
                        {{ $item['label'] }}
                    </span>
                @else
                    <a class="transition hover:text-amber-700" href="{{ $item['url'] }}">
                        {{ $item['label'] }}
                    </a>
                @endif
            @endforeach
        </nav>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-4 w-full">
        @if ($title)
            <div class="w-full">
                <h1 class="text-xl font-semibold text-slate-900">
                    {{ $title }}
                </h1>
                @if ($description)
                    <p class="text-sm text-slate-600">
                        {{ $description }}
                    </p>
                @endif
            </div>
            <div class="h-px w-full bg-slate-200/90"></div>
        @endif
        <div class="w-full flex flex-wrap items-center gap-2 text-sm text-slate-700 {{ $justify }}">
            {{ $slot }}
        </div>
    </div>
</section>
