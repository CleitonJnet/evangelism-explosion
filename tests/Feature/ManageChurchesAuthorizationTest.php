<?php

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Gate;

function createUserWithRole(string $roleName): User
{
    $user = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => $roleName]);
    $user->roles()->syncWithoutDetaching([$role->id]);

    return $user;
}

it('allows manageChurches for teacher director and fieldworker roles', function (string $role): void {
    $user = createUserWithRole($role);

    expect(Gate::forUser($user)->allows('manageChurches'))->toBeTrue();
})->with([
    'Teacher',
    'Director',
    'FieldWorker',
]);

it('denies manageChurches for unrelated roles', function (string $role): void {
    $user = createUserWithRole($role);

    expect(Gate::forUser($user)->allows('manageChurches'))->toBeFalse();
})->with([
    'Board',
    'Facilitator',
    'Mentor',
    'Student',
]);

it('protects church triage routes with manageChurches gate middleware', function (string $routeName): void {
    $route = app('router')->getRoutes()->getByName($routeName);

    expect($route)->not->toBeNull();
    expect($route->gatherMiddleware())->toContain('can:manageChurches');
})->with([
    'app.director.church.index',
    'app.director.church.make_host',
    'app.teacher.churches.index',
    'app.teacher.church.make_host',
]);
