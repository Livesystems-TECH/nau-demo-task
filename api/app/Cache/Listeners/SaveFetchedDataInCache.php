<?php

namespace App\Cache\Listeners;

use App\Cache\Events\CacheMissing;

class SaveFetchedDataInCache
{
    public function handle(CacheMissing $event)
    {
        $event->model::update($event->id, $event->data);
    }
}
