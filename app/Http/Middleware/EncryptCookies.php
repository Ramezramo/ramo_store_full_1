<?php

namespace App\Http\Middleware;

use Illuminate\Cookie\Middleware\EncryptCookies as Middleware;

class EncryptCookies extends Middleware
{
    /**
     * The names of the cookies that should not be encrypted.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Plain 0/1 flag read by the client-side Service Worker page cache
        // (public/sw.js) — must stay unencrypted and JS-readable.
        'ramo_auth_flag',
    ];
}
