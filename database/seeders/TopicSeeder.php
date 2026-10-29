<?php

namespace Database\Seeders;

use App\Models\Topic;
use Illuminate\Database\Seeder;

class TopicSeeder extends Seeder
{
    /**
     * The topics students practise with, each with its own colour.
     *
     * @var array<string, string>
     */
    private const TOPICS = [
        'Eloquent' => 'primary',
        'Relaties' => 'info',
        'Controllers' => 'success',
        'Routes' => 'warning',
        'Validatie' => 'danger',
        'Migrations' => 'gray',
        'Policies' => 'primary',
    ];

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (self::TOPICS as $name => $color) {
            Topic::query()->updateOrCreate(
                ['name' => $name],
                ['color' => $color],
            );
        }
    }
}
