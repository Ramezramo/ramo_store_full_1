<?php

namespace App\Notifications;

use App\Constants\AppConstants;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Lang;

/**
 * --------------------------------------------------------------
 *  BASE URL – change this once and all links will use it
 *  Example: 'https://myapp.com' or 'https://staging.example.org'
 *  --------------------------------------------------------------
 */
class CustomResetPassword extends Notification
{
    public static $baseUrl = AppConstants::DOMAIN; // Change this in production

    public $token;
    public $language;

    public function __construct($token, $language = 'ar')
    {
        $this->token = $token;
        $this->language = in_array($language, ['ar', 'en']) ? $language : 'ar';
    }

    public function via($notifiable)
    {
        return [MailtrapChannel::class];
    }

    public function toMailtrap($notifiable)
    {
        if (empty($notifiable->email)) {
            // \Log::error('Notifiable email is empty or invalid');
            throw new \Exception('Invalid email address for password reset notification');
        }

        $isArabic = $this->language === 'ar';
        $direction = $isArabic ? 'rtl' : 'ltr';
        $align = $isArabic ? 'right' : 'left';

        $resetUrl = static::$baseUrl . '/api/v2/reset-password-page?T=' . urlencode($this->token);
        // \Log::info('Reset URL: ' . $resetUrl);

        $translations = $isArabic ? [
            'title' => 'إعادة تعيين كلمة المرور',
            'greeting' => 'لقد تلقينا طلبًا لإعادة تعيين كلمة المرور لحسابك.',
            'ignore' => 'إذا لم تكن أنت، تجاهل هذا البريد.',
            'button' => 'إعادة تعيين كلمة المرور',
            'expires' => 'ينتهي هذا الرابط خلال 60 دقيقة.',
            'support' => 'هل تحتاج إلى مساعدة؟',
            'contact' => 'اتصل بنا',
            'subject' => 'إعادة تعيين كلمة المرور',
        ] : [
            'title' => 'Reset Your Password',
            'greeting' => 'We received a request to reset your account password.',
            'ignore' => 'If you didn’t request this, you can safely ignore this email.',
            'button' => 'Reset Password',
            'expires' => 'This link will expire in 60 minutes.',
            'support' => 'Need help?',
            'contact' => 'Contact us',
            'subject' => 'Password Reset Request',
        ];

        $supportUrl = static::$baseUrl . '/support';

        $html = <<<EOD
        <!DOCTYPE html>
        <html lang="{$this->language}" dir="{$direction}">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>{$translations['title']}</title>
            <style>
                body { font-family: Arial, sans-serif; direction: {$direction}; text-align: {$align}; margin: 0; padding: 20px; background: #f9f9f9; }
                .container { max-width: 600px; margin: auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
                h2 { color: #333; }
                p { color: #555; line-height: 1.6; }
                .button { display: inline-block; background: #007bff; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: bold; margin: 15px 0; }
                .footer { margin-top: 30px; font-size: 0.9em; color: #777; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>{$translations['title']}</h2>
                <p>{$translations['greeting']}</p>
                <p>{$translations['ignore']}</p>
                <a href="{$resetUrl}" class="button">{$translations['button']}</a>
                <p>{$translations['expires']}</p>
                <p class="footer">
                    {$translations['support']} <a href="{$supportUrl}">{$translations['contact']}</a>
                </p>
            </div>
        </body>
        </html>
        EOD;

        return [
            'subject' => $translations['subject'],
            'html' => $html,
            'email' => $notifiable->email,
        ];
    }

    public function toMail($notifiable)
    {
        $isArabic = $this->language === 'ar';
        $resetUrl = static::$baseUrl . '/api/v2/reset-password-page?T=' . urlencode($this->token);

        $message = (new MailMessage)
            ->subject($isArabic ? 'إعادة تعيين كلمة المرور' : 'Password Reset Request')
            ->line($isArabic
                ? 'لقد تلقينا طلبًا لإعادة تعيين كلمة المرور لحسابك.'
                : 'We received a request to reset your account password.'
            )
            ->action(
                $isArabic ? 'إعادة تعيين كلمة المرور' : 'Reset Password',
                $resetUrl
            )
            ->line($isArabic
                ? 'إذا لم تكن أنت من طلب إعادة التعيين، لا حاجة لاتخاذ أي إجراء.'
                : 'If you didn’t request this, no further action is required.'
            );

        if (!$isArabic) {
            $message->line('This link will expire in 60 minutes.');
        }

        return $message;
    }
}

class MailtrapChannel
{
    public function send($notifiable, Notification $notification)
    {
        $message = $notification->toMailtrap($notifiable);

        $apiToken = env('MAILTRAP_API_TOKEN', 'not-set');
        // \Log::info('Mailtrap API Token: ' . substr($apiToken, 0, 5) . '...');
        // \Log::info('Sending to: ' . $message['email']);
        // \Log::info('Mailtrap Payload: ' . json_encode($message, JSON_UNESCAPED_UNICODE));

        if ($apiToken === 'not-set' || empty($apiToken)) {
            // \Log::error('Mailtrap API token is missing or invalid');
            throw new \Exception('Mailtrap API token is missing or invalid');
        }

        $payload = [
            'from' => [
                'email' => 'hello@demomailtrap.co',
                'name' => 'Ramez Malak',
            ],
            'to' => [
                ['email' => $message['email']],
            ],
            'subject' => $message['subject'],
            'category' => 'Password Reset',
            'html' => $message['html'],
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiToken,
            'Content-Type' => 'application/json',
        ])->post('https://send.api.mailtrap.io/api/send', $payload);

        // \Log::info('Mailtrap API Response: ' . $response->body());

        if ($response->failed()) {
            // \Log::error('Failed to send email via Mailtrap API: ' . $response->body());
            throw new \Exception('Failed to send email via Mailtrap API: ' . $response->body());
        }
    }
}