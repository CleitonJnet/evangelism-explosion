<?php

use App\Models\Role;
use App\Models\User;
use App\Services\Portals\UserPortalResolver;
use App\Support\Portals\Enums\Portal;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

uses(TestCase::class, RefreshDatabase::class);

it('resolves accessible portals and suggested default from existing roles', function () {
    $user = User::factory()->create();
    $roleIds = collect(['Director', 'Teacher', 'Student'])
        ->map(fn (string $name): int => Role::query()->firstOrCreate(['name' => $name])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());

    $resolver = app(UserPortalResolver::class);
    $resolvedPortals = collect($resolver->resolve($user));

    expect($resolvedPortals->pluck('key')->all())->toBe(['base', 'staff', 'student'])
        ->and($resolver->suggestedDefault($user))->toBe(Portal::Staff)
        ->and($resolvedPortals->firstWhere('key', 'staff')?->isSuggestedDefault)->toBeTrue();
});

it('does not expose portals when the user has no mapped role', function () {
    $user = User::factory()->create();

    $resolver = app(UserPortalResolver::class);

    expect($resolver->resolve($user))->toBe([])
        ->and($resolver->suggestedDefault($user))->toBeNull();
});
