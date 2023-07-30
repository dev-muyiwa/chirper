<?php

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "title" => fake()->realText(),
            "media" => array(fake()->imageUrl(), fake()->imageUrl(), fake()->imageUrl()),
            "is_draft" => fake()->boolean(),
//            "user_id" => "99c3d38c-db57-4b6e-a5f9-a711e3a52c3c" // This has to change to match an existing user ID.
        ];
    }


}
