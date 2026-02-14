<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Package;

class UserObserver
{
    /**
     * Handle the User "creating" event.
     */
    public function creating(User $user): void
    {
        // If no package is set, assign Free Trial package
        if (!$user->package_id) {
            $freeTrialPackage = Package::where('package_name', 'Free Trial')->first();
            if ($freeTrialPackage) {
                $user->package_id = $freeTrialPackage->id;
            }
        }

        // Set assessments_allowed based on package
        if ($user->package_id && !$user->assessments_allowed) {
            $package = Package::find($user->package_id);
            if ($package) {
                $user->assessments_allowed = $package->assessments_allowed;
            }
        }

        // Ensure role is set
        if (!$user->role) {
            $user->role = 'tester';
        }

        // Ensure status is set
        if (!$user->status) {
            // Set status based on package price
            if ($user->package_id) {
                $package = Package::find($user->package_id);
                $user->status = ($package && $package->price == 0) ? 'trial' : 'active';
            } else {
                $user->status = 'trial';
            }
        }
    }

    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // You can send welcome email here in the future
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // If package changed, update assessments_allowed
        if ($user->isDirty('package_id') && $user->package) {
            $user->assessments_allowed = $user->package->assessments_allowed;
            $user->saveQuietly(); // Save without triggering events
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        //
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        //
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        //
    }
}
