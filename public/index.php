<?php

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

/*
|--------------------------------------------------------------------------
| Reverse-proxy path prefix support (preview hosting)
|--------------------------------------------------------------------------
|
| When this app is served behind a path-prefixing proxy (e.g. .../port/5000/),
| the proxy strips the prefix before the request reaches PHP. We recover the
| prefix from a query parameter (first hit) or the Referer header (subsequent
| navigations) and rewrite outgoing URLs so links, assets and redirects stay
| inside the proxied path. Disabled entirely when no prefix is detected.
|
*/

$ramoPrefix = null;

if (!empty($_GET['_pfx'])) {
    $ramoPrefix = rtrim((string) $_GET['_pfx'], '/');
} elseif (!empty($_SERVER['HTTP_REFERER'])) {
    $ref = $_SERVER['HTTP_REFERER'];
    if (preg_match('#^(https?://[^/]+.*?/port/\d+)(/|$|\?)#', $ref, $m)) {
        $ramoPrefix = $m[1];
    }
}

if ($ramoPrefix) {
    if (!preg_match('#^https?://#', $ramoPrefix)) {
        $ramoPrefix = null;
    } else {
        $GLOBALS['RAMO_PROXY_PREFIX'] = $ramoPrefix;
    }
}

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Kernel::class);

$request = Request::capture();

if (!empty($GLOBALS['RAMO_PROXY_PREFIX'])) {
    $app->booted(function ($app) {
        $app['url']->forceRootUrl($GLOBALS['RAMO_PROXY_PREFIX']);
        if (str_starts_with($GLOBALS['RAMO_PROXY_PREFIX'], 'https://')) {
            $app['url']->forceScheme('https');
        }
    });
}

$response = $kernel->handle($request);

if (!empty($GLOBALS['RAMO_PROXY_PREFIX'])) {
    $prefix = $GLOBALS['RAMO_PROXY_PREFIX'];
    $localHost = $request->getSchemeAndHttpHost();

    // Fix redirect targets
    $loc = $response->headers->get('Location');
    if ($loc !== null) {
        if (str_starts_with($loc, $localHost)) {
            $loc = $prefix.substr($loc, strlen($localHost));
        } elseif (str_starts_with($loc, '/')) {
            $loc = $prefix.$loc;
        }
        $response->headers->set('Location', $loc);
    }

    // Rewrite HTML/JS bodies
    $ctype = (string) $response->headers->get('Content-Type');
    if ($ctype === '' || str_contains($ctype, 'text/html') || str_contains($ctype, 'javascript') || str_contains($ctype, 'json')) {
        $content = $response->getContent();
        if (is_string($content) && $content !== '') {
            $content = str_replace($localHost, $prefix, $content);
            $content = preg_replace(
                '#\b(href|src|action|data-url|data-href)=(["\'])/(?!/)#i',
                '$1=$2'.$prefix.'/',
                $content
            );
            // Inject a runtime shim so JS-built fetch/XHR paths also stay in-prefix
            $shim = '<script>(function(){var P='.json_encode($prefix).';'
                .'function fix(u){try{if(typeof u==="string"&&u.charAt(0)==="/"&&u.charAt(1)!=="/"){return P+u;}}catch(e){}return u;}'
                .'var of=window.fetch;if(of){window.fetch=function(i,o){if(typeof i==="string"){i=fix(i);}else if(i&&i.url){i=new Request(fix(i.url),i);}return of.call(this,i,o);};}'
                .'var ox=XMLHttpRequest.prototype.open;XMLHttpRequest.prototype.open=function(m,u){return ox.apply(this,[m,fix(u)].concat([].slice.call(arguments,2)));};'
                .'})();</script>';
            if (stripos($content, '</head>') !== false) {
                $content = preg_replace('#</head>#i', $shim.'</head>', $content, 1);
            }
            $response->setContent($content);
        }
    }
}

$response->send();

$kernel->terminate($request, $response);
