# From the project root: .\scripts\test-pages.ps1 -Page 'guest home'
# Use a dedicated PostgreSQL test database with the store schema and no customer data.
[CmdletBinding()]
param(
    [string]$Page,
    [string]$Database = 'ramo_pages_test',
    [string]$Php = 'php'
)

$ErrorActionPreference = 'Stop'
if ($Database -notmatch '_test(?:_|$)') { throw 'Choose a dedicated database with _test in its name.' }
$phpPath = (Get-Command $Php -CommandType Application -ErrorAction Stop).Source
$previousDatabase = $env:DB_DATABASE
$previousStorage = $env:LARAVEL_STORAGE_PATH
Push-Location (Split-Path $PSScriptRoot -Parent)
try {
    $env:DB_DATABASE = $Database
    $env:LARAVEL_STORAGE_PATH = Join-Path ([IO.Path]::GetTempPath()) 'ramo-website-page-tests'
    foreach ($directory in 'framework/views','framework/testing','framework/sessions','framework/cache/data','logs') {
        New-Item -ItemType Directory -Force -Path (Join-Path $env:LARAVEL_STORAGE_PATH $directory) | Out-Null
    }
    $options = @('--testdox','--do-not-cache-result','--fail-on-skipped','--fail-on-empty-test-suite')
    if ($Page) { $options += @('--filter', [regex]::Escape($Page)) }
    & $phpPath vendor/bin/phpunit @options tests/Feature/WebsitePagesTest.php
    $testExit = $LASTEXITCODE
} finally {
    $env:DB_DATABASE = $previousDatabase
    $env:LARAVEL_STORAGE_PATH = $previousStorage
    Pop-Location
}
exit $testExit
