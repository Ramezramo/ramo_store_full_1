<?php

namespace App\Services;

use App\Models\User;

class ReferralFraudChecker
{
    public function isSelfReferral(User $referrer, User $newUser, ?string $ip = null): bool
    {
        return $this->evaluate($referrer, $newUser, $ip)['hard_reject'];
    }

    public function evaluate(User $referrer, User $newUser, ?string $ip = null): array
    {
        $reasons = [];
        $ip = $ip ?: request()->ip();

        if ($ip && $referrer->referral_lock_ip && hash_equals((string) $referrer->referral_lock_ip, (string) $ip)) {
            $reasons[] = 'registration_ip_matches_referrer';
        }

        $referrerPhone = $this->normalizePhone($referrer->phone ?? null);
        $newPhone = $this->normalizePhone($newUser->phone ?? null);
        if ($referrerPhone !== '' && $newPhone !== '' && hash_equals($referrerPhone, $newPhone)) {
            $reasons[] = 'phone_matches_referrer';
        }

        $referrerEmail = strtolower(trim((string) ($referrer->email ?? '')));
        $newEmail = strtolower(trim((string) ($newUser->email ?? '')));
        if ($referrerEmail !== '' && $newEmail !== '' && hash_equals($referrerEmail, $newEmail)) {
            $reasons[] = 'email_matches_referrer';
        } elseif ($this->looksLikeEmailAlias($referrerEmail, $newEmail)) {
            // Alias patterns are review signals, not automatic rejections.
            $reasons[] = 'similar_email_requires_review';
        }

        if ($this->sameShippingAddress($referrer->shipping ?? null, $newUser->shipping ?? null)) {
            $reasons[] = 'shipping_address_matches_referrer';
        }

        $hardRejectReasons = [
            'registration_ip_matches_referrer',
            'phone_matches_referrer',
            'email_matches_referrer',
            'shipping_address_matches_referrer',
        ];

        $hardReject = (bool) array_intersect($reasons, $hardRejectReasons);

        return [
            'hard_reject' => $hardReject,
            'reasons' => $reasons,
            'review_reason' => in_array('similar_email_requires_review', $reasons, true)
                ? 'similar_email_requires_review'
                : null,
        ];
    }

    private function normalizePhone(?string $phone): string
    {
        return preg_replace('/\D+/', '', (string) $phone) ?? '';
    }

    private function looksLikeEmailAlias(string $first, string $second): bool
    {
        if ($first === '' || $second === '' || ! str_contains($first, '@') || ! str_contains($second, '@')) {
            return false;
        }

        [$firstLocal, $firstDomain] = explode('@', $first, 2);
        [$secondLocal, $secondDomain] = explode('@', $second, 2);
        if ($firstDomain !== $secondDomain) {
            return false;
        }

        if (strtolower($firstDomain) === 'gmail.com') {
            $firstLocal = strtolower(strtok($firstLocal, '+'));
            $secondLocal = strtolower(strtok($secondLocal, '+'));
            return str_replace('.', '', $firstLocal) === str_replace('.', '', $secondLocal);
        }

        return strtolower(strtok($firstLocal, '+')) === strtolower(strtok($secondLocal, '+'));
    }

    private function sameShippingAddress(mixed $first, mixed $second): bool
    {
        $first = $this->decodeShipping($first);
        $second = $this->decodeShipping($second);
        if (! $first || ! $second) {
            return false;
        }

        $firstAddress = $this->normalizeText($first['address_1'] ?? $first['address'] ?? '');
        $secondAddress = $this->normalizeText($second['address_1'] ?? $second['address'] ?? '');
        $firstCity = $this->normalizeText($first['city'] ?? '');
        $secondCity = $this->normalizeText($second['city'] ?? '');

        return $firstAddress !== '' && $firstCity !== ''
            && hash_equals($firstAddress, $secondAddress)
            && hash_equals($firstCity, $secondCity);
    }

    private function decodeShipping(mixed $shipping): array
    {
        if (is_array($shipping)) {
            return $shipping;
        }

        if (! is_string($shipping) || trim($shipping) === '') {
            return [];
        }

        $decoded = json_decode($shipping, true);
        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeText(mixed $value): string
    {
        $value = preg_replace('/\s+/u', ' ', trim((string) $value)) ?? '';
        return function_exists('mb_strtolower') ? mb_strtolower($value) : strtolower($value);
    }
}
