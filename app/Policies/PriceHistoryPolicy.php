<?php

namespace App\Policies;

class PriceHistoryPolicy extends BasePosPolicy
{
    public function create($user): bool
    {
        return false;
    }

    public function update($user, mixed $model): bool
    {
        return false;
    }

    public function delete($user, mixed $model): bool
    {
        return false;
    }

    public function deleteAny($user): bool
    {
        return false;
    }
}
