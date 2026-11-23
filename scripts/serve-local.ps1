# Start the store on the local network using PHP's development server.
[CmdletBinding()]
param(
    [string]$Php = 'php',
    [ValidateRange(1, 65535)][int]$Port = 8000
)

$ErrorActionPreference = 'Stop'
$phpPath = (Get-Command $Php -CommandType Application -ErrorAction Stop).Source
$public = Join-Path (Split-Path $PSScriptRoot -Parent) 'public'
$router = '../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php'
if (-not (Test-Path (Join-Path $public $router))) {
    throw 'Install the Laravel dependencies before starting the store.'
}
if ([Net.NetworkInformation.IPGlobalProperties]::GetIPGlobalProperties().GetActiveTcpListeners().Port -contains $Port) {
    throw "Port $Port is already in use."
}

$options = @('-d', 'zend_extension=opcache', '-d', 'opcache.enable_cli=1',
             '-d', "opcache.cache_id=ramo_store_dev_$Port")
& $phpPath @options -r 'exit(function_exists("opcache_get_status") && opcache_get_status(false) ? 0 : 1);'
if ($LASTEXITCODE -ne 0) { throw 'PHP could not enable OPcache.' }
$server = Start-Process -FilePath $phpPath -ArgumentList ($options + @('-S', "0.0.0.0:$Port", $router)) -WorkingDirectory $public -WindowStyle Hidden -PassThru
if ($server.WaitForExit(700)) { throw "PHP exited during startup (code $($server.ExitCode))." }
Write-Output "Ramo Store started on port $Port with OPcache (PID $($server.Id))."
