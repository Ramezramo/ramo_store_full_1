<?php
$root = __DIR__ . '/public';
$uri = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
$file = $root . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file) && basename($file) !== 'index.php') {
    return false;
}
$_SERVER['SCRIPT_NAME'] = '/index.php';
$_SERVER['SCRIPT_FILENAME'] = $root . '/index.php';
require_once $root . '/index.php';
