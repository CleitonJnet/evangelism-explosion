<?php

namespace App\Services\Portals;

use App\Models\User;
use App\Support\Portals\Data\ResolvedPortalData;
use App\Support\Portals\Enums\Portal;
use Illuminate\Support\Collection;

class UserPortalResolver
{
    /**
     * @return array<int, ResolvedPortalData>
     */
    public function resolve(User $user): array
    {
        $suggestedDefault = $this->suggestedDefault($user);

        return collect(Portal::defaultOrder())
            ->filter(fn (Portal $portal): bool => $this->canAccess($user, $portal))
            ->map(fn (Portal $portal): ResolvedPortalData => new ResolvedPortalData(
                portal: $portal,
                key: $portal->key(),
                label: $portal->label(),
                icon: $portal->icon(),
                entryRoute: $portal->entryRoute(),
                description: $portal->description(),
                isSuggestedDefault: $portal === $suggestedDefault,
            ))
            ->values()
            ->all();
    }

    public function canAccess(User $user, Portal $portal): bool
    {
        return $this->roleNames($user)
            ->intersect($this->allowedRoles($portal))
            ->isNotEmpty();
    }

    public function suggestedDefault(User $user): ?Portal
    {
        $roles = $this->roleNames($user);

        foreach ($this->defaultPortalPriority() as $roleName => $portal) {
            if ($roles->contains($roleName) && $this->canAccess($user, $portal)) {
                return $portal;
            }
        }

        return collect(Portal::defaultOrder())
            ->first(fn (Portal $portal): bool => $this->canAccess($user, $portal));
    }

    /**
     * @return Collection<int, string>
     */
    protected function roleNames(User $user): Collection
    {
        if ($user->relationLoaded('roles')) {
            return $user->roles
                ->pluck('name')
                ->filter()
                ->values();
        }

        return $user->roles()
            ->pluck('name')
            ->filter()
            ->values();
    }

    /**
     * @return array<int, string>
     */
    protected function allowedRoles(Portal $portal): array
    {
        return match ($portal) {
            Portal::Base => ['Director', 'Teacher', 'Facilitator', 'Mentor', 'FieldWorker'],
            Portal::Staff => ['Board', 'Director', 'FieldWorker'],
            Portal::Student => ['Student'],
        };
    }

    /**
     * @return array<string, Portal>
     */
    protected function defaultPortalPriority(): array
    {
        return [
            'Board' => Portal::Staff,
            'Director' => Portal::Staff,
            'FieldWorker' => Portal::Staff,
            'Teacher' => Portal::Base,
            'Facilitator' => Portal::Base,
            'Mentor' => Portal::Base,
            'Student' => Portal::Student,
        ];
    }
}
