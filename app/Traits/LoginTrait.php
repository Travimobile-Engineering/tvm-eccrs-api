<?php

namespace App\Traits;

use App\Enums\UserStatus;
use App\Enums\UserType;

trait LoginTrait
{
    protected function authCheck($user)
    {
        if (! $user->email_verified) {
            return $this->error(null, 'Email has not been verified!', 400);
        }

        if ($user->status === null && ! in_array($user->status, UserStatus::cases())) {
            return $this->error(null, 'Account status is unknown!', 400);
        }

        if ($user->status->isInactive()) {
            return $this->error(null, 'Account is inactive!', 400);
        }

        if ($user->status->isBlocked()) {
            return $this->error(null, 'Account is blocked!', 400);
        }

        if ($user->status->isPending()) {
            return $this->error(null, 'Account is pending!', 400);
        }

        if ($user->status->isDeleted()) {
            return $this->error(null, 'Account is deleted!', 400);
        }
    }

    protected function additionalData($user): array
    {
        return match ($user->user_category) {
            UserType::SUPER_ADMIN->value => [
                'role' => $user->roles()->pluck('name'),
                'permissions' => $user->roles()->with('permissions')
                    ->get()
                    ->pluck('permissions.*.name')
                    ->flatten()
                    ->unique()
                    ->values()
                    ->toArray(),
            ],
            default => [],
        };
    }
}
