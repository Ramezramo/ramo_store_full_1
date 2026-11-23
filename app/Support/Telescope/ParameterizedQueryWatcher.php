<?php

namespace App\Support\Telescope;

use Laravel\Telescope\Watchers\QueryWatcher;

class ParameterizedQueryWatcher extends QueryWatcher
{
    public function replaceBindings($event)
    {
        return $event->sql;
    }
}
