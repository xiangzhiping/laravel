<?php

namespace App\Listeners;

use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Facades\Log;

class QueryExecuteListener
{
    /**
     * Handle the event.
     *
     * @param  QueryExecuted  $event
     * @return void
     */
    public function handle($event)
    {
        if(config('app.debug') == true) {
            $sql = str_replace(array('%', '?'), array('%%', '"%s"'), $event->sql);
            $sql = vsprintf($sql, $event->bindings);
            foreach ($event->bindings as $key => $value) {
                if (is_string($key) && $key{0} == ':') {
                    $sql = str_replace($key, "'" . $value . "'", $sql);
                }
            }
            Log::info($sql . ' [time: ' . ($event->time/1000) . ']');
        }
    }
}
