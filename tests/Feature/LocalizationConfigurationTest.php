<?php

it('uses brazilian portuguese as default application locale', function (): void {
    expect(config('app.locale'))->toBe('pt_BR')
        ->and(config('app.fallback_locale'))->toBe('pt_BR')
        ->and(config('app.faker_locale'))->toBe('pt_BR');
});

it('loads portuguese authentication messages', function (): void {
    app()->setLocale('pt_BR');

    expect(__('auth.failed'))->toContain('credenciais');
});
