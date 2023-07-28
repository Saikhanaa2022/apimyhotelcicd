<?php

namespace App\Observers;

use App\Models\UserClone;

class UserCloneObserver
{
    /**
     * Handle the user clone "created" event.
     *
     * @param  \App\Models\UserClone  $userClone
     * @return void
     */
    public function created(UserClone $userClone)
    {
        //
    }

    /**
     * Handle the user clone "updated" event.
     *
     * @param  \App\Models\UserClone  $userClone
     * @return void
     */
    public function updated(UserClone $userClone)
    {
        //
    }

    /**
     * Handle the user clone "deleted" event.
     *
     * @param  \App\Models\UserClone  $userClone
     * @return void
     */
    public function deleted(UserClone $userClone)
    {
        //
    }

    /**
     * Handle the user clone "restored" event.
     *
     * @param  \App\Models\UserClone  $userClone
     * @return void
     */
    public function restored(UserClone $userClone)
    {
        //
    }

    /**
     * Handle the user clone "force deleted" event.
     *
     * @param  \App\Models\UserClone  $userClone
     * @return void
     */
    public function forceDeleted(UserClone $userClone)
    {
        //
    }
}
