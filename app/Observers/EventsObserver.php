<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\Events;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EventsObserver
{
    public function created(Events $event)
    {
        Log::info('Event created observer triggered for event ID: ' . $event->id);
        DB::connection('mongodb')->collection('events_changes')->insert([
            'event_id' => $event->id,
            'action' => 'created',
            'new_data' => $event->toArray(),
            'changed_at' => now(),
        ]);
    }

    public function updated(Events $event)
    {
        Log::info('Event updated observer triggered for event ID: ' . $event->id);
        DB::connection('mongodb')->collection('events_changes')->insert([
            'event_id' => $event->id,
            'action' => 'updated',
            'new_data' => $event->getChanges(),
            'changed_at' => now(),
        ]);
    }

    public function deleted(Events $event)
    {
        Log::info('Event deleted observer triggered for event ID: ' . $event->id);
        DB::connection('mongodb')->collection('events_changes')->insert([
            'event_id' => $event->id,
            'action' => 'deleted',
            'changed_at' => now(),
        ]);
    }
}
