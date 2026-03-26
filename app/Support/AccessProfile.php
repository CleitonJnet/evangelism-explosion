<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class AccessProfile
{
    public const SESSION_KEY = 'last_access_profile';

    public const COOKIE_KEY = 'last_access_profiles';

    /**
     * @var array<string, string>
     */
    private const ROLE_HOME_ROUTES = [
        'Board' => 'app.board.dashboard',
        'Director' => 'app.director.dashboard',
        'FieldWorker' => 'app.fieldworker.dashboard',
        'Teacher' => 'app.teacher.dashboard',
        'Facilitator' => 'app.facilitator.dashboard',
        'Mentor' => 'app.mentor.dashboard',
        'Student' => 'app.student.dashboard',
    ];

    public static function roleFromRouteName(?string $routeName): ?string
    {
        if ($routeName === null) {
            return null;
        }

        foreach (self::ROLE_HOME_ROUTES as $role => $homeRoute) {
            $prefix = str_replace('.dashboard', '.', $homeRoute);

            if (str_starts_with($routeName, $prefix)) {
                return $role;
            }
        }

        return null;
    }

    public static function homeRouteForRole(string $role): ?string
    {
        return self::ROLE_HOME_ROUTES[$role] ?? null;
    }

    public static function remember(Request $request, User $user, string $role): void
    {
        $request->session()->put(self::SESSION_KEY, $role);

        $cookieProfiles = self::cookieProfilesFromRequest($request);
        $cookieProfiles[(string) $user->getKey()] = $role;

        Cookie::queue(Cookie::forever(self::COOKIE_KEY, json_encode($cookieProfiles)));
    }

    public static function rememberedRole(Request $request, User $user): ?string
    {
        $sessionRole = $request->session()->get(self::SESSION_KEY);

        if (is_string($sessionRole) && self::userHasRole($user, $sessionRole)) {
            return $sessionRole;
        }

        $cookieProfiles = self::cookieProfilesFromRequest($request);
        $cookieRole = $cookieProfiles[(string) $user->getKey()] ?? null;

        return is_string($cookieRole) && self::userHasRole($user, $cookieRole)
            ? $cookieRole
            : null;
    }

    public static function userHasRole(User $user, string $role): bool
    {
        return array_key_exists($role, self::ROLE_HOME_ROUTES) && $user->hasRole($role);
    }

    /**
     * @return array<string, string>
     */
    private static function cookieProfilesFromRequest(Request $request): array
    {
        $rawCookie = $request->cookie(self::COOKIE_KEY);

        if (! is_string($rawCookie) || trim($rawCookie) === '') {
            return [];
        }

        $decoded = json_decode($rawCookie, true);

        if (! is_array($decoded)) {
            return [];
        }

        return collect($decoded)
            ->mapWithKeys(function (mixed $role, mixed $userId): array {
                if (! is_string($role) || (! is_string($userId) && ! is_int($userId))) {
                    return [];
                }

                return [(string) $userId => $role];
            })
            ->all();
    }
}
