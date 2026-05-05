<?php
header('Content-Type: application/json');


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = trim(str_replace('/store-getter', '', $uri), '/');

echo json_encode([
    "message" => " Welcome to the Ramo Store Native PHP Products API. You have accessed the endpoint: /" . $uri,
    "time" => date('Y-m-d H:i:s')
]); 