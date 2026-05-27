<?php

namespace Database\Factories;

use App\Enums\GameStatus;
use App\Models\Game;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Game>
 */
class GameFactory extends Factory
{
    protected $model = Game::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => Str::title($name),
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 99999),
            'description' => fake()->sentence(),
            'tags' => fake()->randomElements(['action', 'rpg', 'sports', 'racing', 'shooter'], 2),
            'status' => fake()->randomElement(GameStatus::cases()),
            'sort_order' => fake()->numberBetween(0, 50),
            'meta_title' => Str::title($name),
            'meta_description' => fake()->sentence(),
        ];
    }
}
