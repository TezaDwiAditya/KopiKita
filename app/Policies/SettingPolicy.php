<?php

namespace App\Policies;

use App\Models\Setting;
use App\Models\User;

class SettingPolicy extends BasePosPolicy
{
    public function create(User $user): bool
    {
        return Setting::query()->doesntExist();
    }

    public function delete(User $user, mixed $model): bool
    {
        return false;
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
