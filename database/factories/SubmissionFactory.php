<?php

namespace Database\Factories;

use App\Models\Submission;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Submission>
 */
class SubmissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(3),
            'filename' => 'Cart.php',
            'code' => "<?php\n\nclass Cart\n{\n    private array \$items = [];\n\n    public function add(string \$item): void\n    {\n        \$this->items[] = \$item;\n    }\n}\n",
        ];
    }
}
