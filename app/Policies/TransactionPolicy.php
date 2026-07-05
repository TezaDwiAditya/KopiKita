<?php

namespace App\Policies;

use App\Models\Transaction;
use App\Models\User;

class TransactionPolicy extends BasePosPolicy
{
    public function delete(User $user, mixed $model): bool
    {
        return $model instanceof Transaction && $model->status === 'draft';
    }

    public function deleteAny(User $user): bool
    {
        return false;
    }
}
