<?php

namespace Database\Seeders;

use App\Models\Badge;
use Illuminate\Database\Seeder;

class BadgeSeeder extends Seeder
{
    /**
     * The five badges: name => [star threshold, icon, colour].
     *
     * Nieuwsgierig and Speurder use the colours from the design; the higher,
     * still-locked badges follow the brand palette (teal, blue, purple).
     *
     * @var array<string, array{int, string, string}>
     */
    private const BADGES = [
        'Nieuwsgierig' => [5, 'lucide-search', '#FFB800'],
        'Speurder' => [15, 'lucide-bug', '#FF8A3D'],
        'Kenner' => [30, 'lucide-book-open', '#0B7A75'],
        'Wijsneus' => [50, 'lucide-lightbulb', '#2563EB'],
        'Eigenwijs' => [75, 'lucide-crown', '#9333EA'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::BADGES as $name => [$threshold, $icon, $color]) {
            Badge::query()->updateOrCreate(
                ['name' => $name],
                ['threshold' => $threshold, 'icon' => $icon, 'color' => $color],
            );
        }
    }
}
