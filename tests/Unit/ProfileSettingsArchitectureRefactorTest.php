<?php

it('keeps the profile settings flow on a single class-based livewire entrypoint', function (): void {
    $projectRoot = dirname(__DIR__, 2);
    $profileComponent = file_get_contents($projectRoot.'/app/Livewire/Pages/App/Settings/Profile.php');
    $profileView = file_get_contents($projectRoot.'/resources/views/livewire/pages/app/settings/profile.blade.php');
    $settingsRoutes = file_get_contents($projectRoot.'/routes/app/settings.php');
    $settingsLayout = file_get_contents($projectRoot.'/resources/views/components/app/settings/layout.blade.php');
    $sidebarMenu = file_get_contents($projectRoot.'/resources/views/components/app/layouts/app/sidebar.blade.php');
    $desktopUserMenu = file_get_contents($projectRoot.'/resources/views/components/app/desktop-user-menu.blade.php');

    expect($profileComponent)->toContain('class Profile extends Component');
    expect($profileView)->not->toContain('new class extends Component');
    expect($profileView)->not->toContain('@volt');
    expect($settingsRoutes)->toContain("Route::get('profile', Profile::class)->name('profile');");
    expect($settingsRoutes)->not->toContain("Volt::route('settings/profile'");
    expect($settingsLayout)->toContain("route('app.profile')");
    expect($settingsLayout)->not->toContain("route('app.profile.edit')");
    expect($sidebarMenu)->toContain("route('app.profile')");
    expect($desktopUserMenu)->toContain("route('app.profile')");
});
