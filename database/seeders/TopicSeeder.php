<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * The seven topics with their chip colours from the design:
     * name => [background, text].
     *
     * @var array<string, array{string, string}>
     */
    private const TOPICS = [
        'Eloquent' => ['#DBEAFE', '#1E40AF'],
        'Relaties' => ['#CFFAFE', '#155E75'],
        'Controllers' => ['#F1EEEA', '#4A4660'],
        'Routes' => ['#FCE7F3', '#9D174D'],
        'Validatie' => ['#DCFCE7', '#166534'],
        'Migrations' => ['#FEF3C7', '#92400E'],
        'Policies' => ['#E0E7FF', '#3730A3'],
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TOPICS as $name => [$color, $textColor]) {
            Topic::query()->updateOrCreate(
                ['name' => $name],
                ['color' => $color, 'text_color' => $textColor],
            );
        }
    }
}
