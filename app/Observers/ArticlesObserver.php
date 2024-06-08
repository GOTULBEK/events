<?php

namespace App\Observers;

use App\Models\Articles;

class ArticlesObserver
{
    /**
     * Handle the Articles "created" event.
     */
    public function created(Articles $articles): void
    {
        //
    }

    /**
     * Handle the Articles "updated" event.
     */
    public function updated(Articles $articles): void
    {
        //
    }

    /**
     * Handle the Articles "deleted" event.
     */
    public function deleted(Articles $articles): void
    {
        //
    }

    /**
     * Handle the Articles "restored" event.
     */
    public function restored(Articles $articles): void
    {
        //
    }

    /**
     * Handle the Articles "force deleted" event.
     */
    public function forceDeleted(Articles $articles): void
    {
        //
    }
}
