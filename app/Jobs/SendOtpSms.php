<?php

namespace App\Jobs;

use App\Models\OtpVerification;
use App\Services\Sms\SmsGateway;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendOtpSms implements ShouldQueue
{
    use Queueable;

    /**
     * Keep delivery attempts bounded. An OTP that cannot be delivered promptly
     * must not keep retrying after its short validity window.
     *
     * @var int
     */
    public int $tries = 2;

    /**
     * Abort the job when the OTP is no longer useful.
     *
     * @var int
     */
    public int $timeout = 15;

    public function __construct(public readonly int $otpVerificationId)
    {
        $this->onQueue('otp');
    }

    public function handle(SmsGateway $smsGateway): void
    {
        $otpVerification = OtpVerification::find($this->otpVerificationId);

        if (! $otpVerification || $otpVerification->verified || $otpVerification->isExpired()) {
            return;
        }

        $smsGateway->send(
            $otpVerification->phone,
            'Your Ramo Store OTP code is '.$otpVerification->otp_code
        );
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Queued OTP SMS send failed', [
            'otp_verification_id' => $this->otpVerificationId,
            'exception' => $exception->getMessage(),
        ]);
    }
}
