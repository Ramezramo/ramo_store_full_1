<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AppConfigController;

/*
|-----------------------------------------------------------------------
| App Config API — intentionally public (no auth middleware)
|-----------------------------------------------------------------------
| These routes are public by design. The app_configs table has an
| `is_public` boolean column, and AppConfigController enforces
| ->where('is_public', true) on every query (index, show, groups).
|
| Only rows explicitly marked is_public = true are ever exposed here.
| If you add a new config row that contains sensitive data (API keys,
| secrets, internal URLs, etc.), you MUST set is_public = false to
| prevent it from being served through these endpoints.
|
| Do NOT add auth middleware to these routes — they are consumed by
| unauthenticated clients (Flutter app initial load, public storefront).
|
| Flutter app usage:
|   GET /api/app-config?lang=en
|   GET /api/app-config/{key}?lang=en
|   GET /api/app-config/groups
|-----------------------------------------------------------------------
*/

Route::prefix('app-config')->group(function () {
    Route::get('/',        [AppConfigController::class, 'index']);
    Route::get('/groups',  [AppConfigController::class, 'groups']);
    Route::get('/{key}',   [AppConfigController::class, 'show']);
});
