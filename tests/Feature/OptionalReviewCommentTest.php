<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OptionalReviewCommentTest extends TestCase
{
    public function test_rating_is_required_but_comment_can_be_empty_or_short_for_web_and_api(): void
    {
        DB::beginTransaction();
        try {
            $suffix = uniqid();
            $user = User::create(['name' => 'Review customer', 'email' => "review-{$suffix}@example.test", 'password' => 'password']);
            $ids = [];
            foreach (range(1, 4) as $i) {
                $ids[] = DB::table('products_data')->insertGetId([
                    'name' => "Review fixture {$suffix} {$i}", 'slug' => "review-{$suffix}-{$i}",
                    'search_text' => 'review fixture', 'images' => '{}', 'status' => 'publish',
                    'acceptance_status' => 'approved', 'created_at' => now(), 'updated_at' => now(),
                ]);
            }
            $this->actingAs($user)->get(route('product', $ids[0]))->assertOk()
                ->assertSee('Your comment (optional)')->assertDontSee('Review Title');
            $this->post(route('review.store'), ['product_id' => $ids[0]])->assertSessionHasErrors('rating');
            $this->post(route('review.store'), ['product_id' => $ids[0], 'rating' => 5])
                ->assertSessionHasNoErrors()->assertRedirect(route('product', $ids[0]));
            $this->assertDatabaseHas('product_reviews', ['user_id' => $user->id, 'product_id' => $ids[0], 'rating' => 5, 'body' => '']);
            $this->post(route('review.store'), ['product_id' => $ids[1], 'rating' => 4, 'body' => 'good'])
                ->assertSessionHasNoErrors()->assertRedirect(route('product', $ids[1]));
            $this->assertDatabaseHas('product_reviews', ['user_id' => $user->id, 'product_id' => $ids[1], 'body' => 'good']);
            $this->actingAs($user, 'sanctum')->postJson('/api/reviews', ['product_id' => $ids[2], 'rating' => 3])->assertCreated();
            $this->assertDatabaseHas('product_reviews', ['user_id' => $user->id, 'product_id' => $ids[2], 'body' => '']);
            $this->postJson('/api/reviews', ['product_id' => $ids[3], 'body' => 'good'])->assertUnprocessable()->assertJsonValidationErrors('rating');
        } finally {
            DB::rollBack();
        }
    }
}
