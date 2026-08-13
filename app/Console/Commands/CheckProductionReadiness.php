<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class CheckProductionReadiness extends Command
{
    /**
     * The name and signature of the console command.
     *
     * This command is intentionally configuration-only. It does not send SMS
     * messages, place orders, or change any production data.
     *
     * @var string
     */
    protected $signature = 'production:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Verify the effective production safety configuration before a release';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $checks = [
            [
                'name' => 'Debug mode is disabled',
                'passed' => ! config('app.debug'),
                'expected' => 'APP_DEBUG=false',
                'actual' => config('app.debug') ? 'true' : 'false',
            ],
            [
                'name' => 'Application URL uses HTTPS',
                'passed' => str_starts_with((string) config('app.url'), 'https://'),
                'expected' => 'https://…',
                'actual' => (string) config('app.url'),
            ],
            [
                'name' => 'Debug diagnostics are disabled',
                'passed' => config('debugbar.enabled') === false,
                'expected' => 'DEBUGBAR_ENABLED=false',
                'actual' => config('debugbar.enabled') ? 'true' : 'false',
            ],
            [
                'name' => 'Trusted proxies are explicit or unused',
                'passed' => ! in_array(config('trustedproxy.proxies'), ['*', '**'], true),
                'expected' => 'specific CDN/load-balancer CIDRs, or empty for a direct HTTPS origin',
                'actual' => config('trustedproxy.proxies') ? 'configured' : 'none',
            ],
            [
                'name' => 'Session cookies require HTTPS',
                'passed' => config('session.secure') === true,
                'expected' => 'SESSION_SECURE_COOKIE=true',
                'actual' => var_export(config('session.secure'), true),
            ],
            [
                'name' => 'Session storage is shared',
                'passed' => ! in_array(config('session.driver'), ['array', 'cookie', 'file'], true),
                'expected' => 'redis, database, memcached, or dynamodb',
                'actual' => (string) config('session.driver'),
            ],
            [
                'name' => 'Cache storage is shared',
                'passed' => ! in_array(config('cache.default'), ['array', 'file', 'null'], true),
                'expected' => 'redis, database, memcached, dynamodb, or apc',
                'actual' => (string) config('cache.default'),
            ],
            [
                'name' => 'Queue work is asynchronous',
                'passed' => ! in_array(config('queue.default'), ['sync', 'null'], true),
                'expected' => 'redis, database, sqs, or another asynchronous driver',
                'actual' => (string) config('queue.default'),
            ],
            [
                'name' => 'Default files use shared/object storage',
                'passed' => ! in_array(config('filesystems.default'), ['local', 'public'], true),
                'expected' => 's3 or another shared persistent disk',
                'actual' => (string) config('filesystems.default'),
            ],
            [
                'name' => 'Development OTP preview is disabled',
                'passed' => config('sms.development_preview') !== true,
                'expected' => 'OTP_DEVELOPMENT_PREVIEW=false',
                'actual' => config('sms.development_preview') ? 'true' : 'false',
            ],
        ];

        $rows = array_map(static fn (array $check): array => [
            $check['passed'] ? 'PASS' : 'FAIL',
            $check['name'],
            $check['actual'],
            $check['expected'],
        ], $checks);

        $this->table(['Status', 'Check', 'Current value', 'Required value'], $rows);

        $smsDriver = strtolower((string) config('sms.driver', 'log'));
        if ($smsDriver === 'log') {
            $this->warn('OTP is still using the visible log-driver development fallback. This is preserved by design until an SMS provider is selected; do not go live with this driver.');
        }

        $failed = collect($checks)->contains(static fn (array $check): bool => ! $check['passed']);

        if ($failed) {
            $this->error('Production configuration check failed. Resolve every failed row before deployment.');

            return self::FAILURE;
        }

        $this->info('Production configuration check passed. Verify provider credentials, queue workers, and health checks separately.');

        return self::SUCCESS;
    }
}
