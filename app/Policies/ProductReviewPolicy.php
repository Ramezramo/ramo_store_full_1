<?php

namespace App\Policies;

use App\Models\ProductReview;
use App\Models\User;

class ProductReviewPolicy
{
    public function delete(User $user, ProductReview $review): bool
    {
        return (int) $review->user_id === (int) $user->getKey();
    }

    public function before(User $user, string $ability): ?bool
    {
        $roles = $user->role;
        if (is_string($roles)) {
            $roles = json_decode($roles, true) ?: [$roles];
        }

        return in_array('admin', (array) $roles, true) ? true : null;
    }
}
