<?php

$proxies = array_values(array_filter(array_map(
    'trim',
    explode(',', (string) env('TRUSTED_PROXIES', ''))
)));

return [
    /*
     * Comma-separated IP addresses or CIDR ranges for the only reverse proxies
     * that may supply X-Forwarded-* headers. Keep this empty on direct local
     * development. Production must use the published CIDRs of its CDN / load
     * balancer and must block direct public access to the origin.
     */
    'proxies' => $proxies === [] ? null : $proxies,
];
