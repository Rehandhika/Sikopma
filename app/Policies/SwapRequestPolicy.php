<?php

namespace App\Policies;

use App\Models\ScheduleChangeRequest;
use App\Models\SwapRequest;
use App\Models\User;

class SwapRequestPolicy
{
    /**
     * Can view any schedule change requests
     */
    public function viewAny(User $user): bool
    {
        return $user->can('create.swap.request');
    }

    /**
     * Can view a specific schedule change request
     */
    public function view(User $user, SwapRequest|ScheduleChangeRequest $request): bool
    {
        return $user->id === $request->user_id ||
               $user->can('view.swap.all');
    }

    /**
     * Can create a schedule change request
     */
    public function create(User $user): bool
    {
        return $user->can('create.swap.request') && $user->isActive();
    }

    /**
     * Can admin respond to a schedule change request
     */
    public function adminRespond(User $user, SwapRequest|ScheduleChangeRequest $request): bool
    {
        return $user->can('approve.swap.admin') &&
               $request->status === 'pending';
    }

    /**
     * Can cancel a schedule change request
     */
    public function cancel(User $user, SwapRequest|ScheduleChangeRequest $request): bool
    {
        return $user->id === $request->user_id &&
               $request->status === 'pending';
    }
}
