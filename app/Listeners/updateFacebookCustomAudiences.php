<?php

namespace App\Listeners;

use App\Events\createEventSourceGroup;
use App\Models\FacebookCustomAudiences;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class updateFacebookCustomAudiences
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(FacebookCustomAudiences $model)
    {
        $this->model = $model;
    }

    /**
     * Handle the event.
     *
     * @param  createEventSourceGroup  $event
     * @return void
     */
    public function handle(createEventSourceGroup $event)
    {
        $customer_id = $event->customer_id;
        $eventSourceGroups = $event->eventSourceGroups;
        $facebookCustomAudiences = $this->model::where('customer_id', $customer_id)
            ->first();
        if($facebookCustomAudiences && isset($eventSourceGroups['id'])) {
            $facebookCustomAudiences->event_source_group_id = $eventSourceGroups['id'];
            $facebookCustomAudiences->save();
        }
    }
}
