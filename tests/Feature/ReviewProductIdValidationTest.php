<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ReviewProductIdValidationTest extends TestCase
{
    public function test_web_review_submission_rejects_a_non_integer_product_id_before_persisting_a_review(): void
    {
        $user = $this->createCustomer();

        try {
            $csrfToken = 'review-validation-csrf';
            $response = $this->actingAs($user)
                ->withSession(['_token' => $csrfToken])
                ->post(route('review.store'), array_merge($this->invalidPayload(), ['_token' => $csrfToken]));

            $response
                ->assertRedirect()
                ->assertSessionHasErrors('product_id');

            $this->assertSame(0, DB::table('product_reviews')->where('user_id', $user->id)->count());
        } finally {
            $user->delete();
        }
    }

    public function test_api_review_submission_rejects_a_non_integer_product_id_before_persisting_a_review(): void
    {
        $user = $this->createCustomer();

        try {
            $response = $this->actingAs($user, 'sanctum')->postJson('/api/reviews', $this->invalidPayload());

            $response
                ->assertUnprocessable()
                ->assertJsonValidationErrors('product_id');

            $this->assertSame(0, DB::table('product_reviews')->where('user_id', $user->id)->count());
        } finally {
            $user->delete();
        }
    }

    /** @return array{product_id:string,rating:int,title:string,body:string} */
    private function invalidPayload(): array
    {
        return [
            'product_id' => '1 OR 1=1',
            'rating' => 5,
            'title' => 'Validation regression',
            'body' => 'This request must fail before review lookup queries execute.',
        ];
    }

    private function createCustomer(): User
    {
        return User::create([
            'name' => 'Review Validation Test',
            'email' => 'review-validation-' . uniqid() . '@ramostore.local',
            'password' => 'temporary-test-password',
            'role' => json_encode(['customer']),
        ]);
    }
}
