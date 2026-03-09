<?php

it('removes dark mode hooks from app layouts and profile settings views', function (): void {
    $projectRoot = dirname(__DIR__, 2);

    $layoutFiles = [
        '/resources/views/components/app/layouts/auth/simple.blade.php',
        '/resources/views/components/app/layouts/auth/card.blade.php',
        '/resources/views/components/app/layouts/auth/split.blade.php',
        '/resources/views/components/app/layouts/app/header.blade.php',
        '/resources/views/components/app/layouts/app/sidebar.blade.php',
    ];

    foreach ($layoutFiles as $layoutFile) {
        $contents = file_get_contents($projectRoot.$layoutFile);

        expect($contents)->not->toContain('class="dark"');
        expect($contents)->not->toContain('dark:');
    }

    $appLinksHead = file_get_contents($projectRoot.'/resources/views/components/layouts/head/app/links.blade.php');
    $settingsLayout = file_get_contents($projectRoot.'/resources/views/components/app/settings/layout.blade.php');
    $profileSettingsPage = file_get_contents($projectRoot.'/resources/views/livewire/pages/app/settings/profile.blade.php');
    $twoFactorSettings = file_get_contents($projectRoot.'/resources/views/livewire/settings/two-factor.blade.php');

    expect($appLinksHead)->not->toContain('@fluxAppearance');
    expect($settingsLayout)->not->toContain("route('app.appearance.edit')");
    expect($profileSettingsPage)->not->toContain('livewire:settings.appearance');
    expect($twoFactorSettings)->not->toContain('$flux.appearance');
    expect($twoFactorSettings)->not->toContain('$flux.dark');
});
