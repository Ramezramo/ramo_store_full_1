<?php

$rawProxies = trim((string) env('TRUSTED_PROXIES', ''));
$proxies = in_array($rawProxies, ['*', '**'], true)
    ? $rawProxies
    : array_values(array_filter(array_map('trim', explode(',', $rawProxies))));

return [
    /*
     * Comma-separated IP addresses or CIDR ranges for reverse proxies that may
     * supply X-Forwarded-* headers. Replit places one ingress proxy in front of
     * this app, so REMOTE_ADDR trusts only that immediate peer by default.
     * Other deployments should set their ingress CIDRs explicitly.
     */
    'proxies' => $proxies === [] ? 'REMOTE_ADDR' : $proxies,
];
