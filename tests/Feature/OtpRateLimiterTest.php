<?php

namespace Tests\Feature;

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Tests\TestCase;

class OtpRateLimiterTest extends TestCase
{
    public function test_otp_send_limits_are_bound_to_ip_and_normalized_phone(): void
    {
        $limiter = app(RateLimiter::class);
        $byIp = $limiter->limiter('otp-send-ip');
        $byPhone = $limiter->limiter('otp-send-phone');

        $ipRequest = Request::create('/auth/send-otp', 'POST', ['phone' => '01000000001']);
        $ipRequest->server->set('REMOTE_ADDR', '203.0.113.120');
        $ipLimit = $byIp($ipRequest);

        $localFormatRequest = Request::create('/auth/send-otp', 'POST', ['phone' => '01000000001']);
        $internationalFormatRequest = Request::create('/auth/send-otp', 'POST', ['phone' => '201000000001']);
        $localPhoneLimit = $byPhone($localFormatRequest);
        $internationalPhoneLimit = $byPhone($internationalFormatRequest);

        $this->assertSame(5, $ipLimit->maxAttempts);
        $this->assertSame(60, $ipLimit->decaySeconds);
        $this->assertSame('otp.send.ip.203.0.113.120', $ipLimit->key);
        $this->assertSame(6, $localPhoneLimit->maxAttempts);
        $this->assertSame(3600, $localPhoneLimit->decaySeconds);
        $this->assertSame($localPhoneLimit->key, $internationalPhoneLimit->key);
        $this->assertStringNotContainsString('01000000001', $localPhoneLimit->key);
    }

    public function test_otp_verification_limits_are_bound_to_ip_and_phone(): void
    {
        $limiter = app(RateLimiter::class);
        $byIp = $limiter->limiter('otp-verify-ip');
        $byPhone = $limiter->limiter('otp-verify-phone');

        $request = Request::create('/auth/verify-otp', 'POST', ['phone' => '01000000001']);
        $request->server->set('REMOTE_ADDR', '203.0.113.121');
        $ipLimit = $byIp($request);
        $phoneLimit = $byPhone($request);

        $this->assertSame(10, $ipLimit->maxAttempts);
        $this->assertSame(60, $ipLimit->decaySeconds);
        $this->assertSame('otp.verify.ip.203.0.113.121', $ipLimit->key);
        $this->assertSame(10, $phoneLimit->maxAttempts);
        $this->assertSame(60, $phoneLimit->decaySeconds);
        $this->assertStringStartsWith('otp.verify.phone.', $phoneLimit->key);
    }
}
