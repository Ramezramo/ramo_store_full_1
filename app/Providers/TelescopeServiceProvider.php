<?php

namespace App\Providers;

use Laravel\Telescope\IncomingEntry;
use Laravel\Telescope\Telescope;
use Laravel\Telescope\TelescopeApplicationServiceProvider;

class TelescopeServiceProvider extends TelescopeApplicationServiceProvider
{
    public function register(): void
    {
        Telescope::filter(function (IncomingEntry $entry): bool {
            unset($entry->content['user']);

            if ($entry->type === 'request') {
                $entry->content['uri'] = '/'.ltrim(request()->route()?->uri() ?? '[unmatched]', '/');
                $entry->content['ip_address'] = '';
                $entry->content['headers'] = [];
                $entry->content['payload'] = [];
                $entry->content['session'] = [];
                $entry->content['response_headers'] = [];
                $entry->content['response'] = '';
            }

            return true;
        });
    }
}
