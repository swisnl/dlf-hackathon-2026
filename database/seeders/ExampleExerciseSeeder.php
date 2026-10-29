<?php

namespace Database\Seeders;

use App\Models\Exercise;
use App\Models\Topic;
use Illuminate\Database\Seeder;

class ExampleExerciseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $topic = Topic::query()->where('name', 'Validatie')->firstOrFail();

        $correctCode = <<<'PHP'
            <?php

            namespace App\Http\Requests;

            use App\Models\Post;
            use Illuminate\Foundation\Http\FormRequest;

            class StorePostRequest extends FormRequest
            {
                public function authorize(): bool
                {
                    return $this->user()->can('create', Post::class);
                }

                public function rules(): array
                {
                    return [
                        'title' => ['required', 'string', 'max:255'],
                        'body' => ['required', 'string'],
                        'category_id' => ['required', 'exists:categories,id'],
                    ];
                }
            }
            PHP;

        $wrongCode = <<<'PHP'
            <?php

            namespace App\Http\Requests;

            use App\Models\Post;
            use Illuminate\Foundation\Http\FormRequest;

            class StorePostRequest extends FormRequest
            {
                public function authorize(): bool
                {
                    return true;
                }

                public function rules(): array
                {
                    return [
                        'title' => ['required', 'string', 'max:255'],
                        'body' => ['string'],
                        'category_id' => ['required', 'integer'],
                    ];
                }
            }
            PHP;

        $exercise = Exercise::query()->updateOrCreate(
            ['title' => 'Validatie in de StorePostRequest'],
            [
                'topic_id' => $topic->id,
                'stars' => 3,
                'correct_code' => $correctCode,
                'wrong_code' => $wrongCode,
                'published_at' => now(),
            ],
        );

        // Line numbers are 1-based and point at the wrong class.
        $errors = [
            [
                'line' => 12,
                'title' => 'authorize() geeft iedereen toegang',
                'hint' => 'Wat gebeurt er als je hier altijd true teruggeeft?',
                'explanation' => 'Met `return true;` mag elke gebruiker dit verzoek doen. Controleer de rechten, bijvoorbeeld met `return $this->user()->can(\'create\', Post::class);`, zodat alleen bevoegde gebruikers een post mogen aanmaken.',
            ],
            [
                'line' => 19,
                'title' => 'body mist de required-regel',
                'hint' => 'Mag het bericht leeg zijn?',
                'explanation' => 'Zonder `required` is `body` optioneel en komt een leeg bericht erdoor. Voeg de regel toe: `\'body\' => [\'required\', \'string\']`.',
            ],
            [
                'line' => 20,
                'title' => 'category_id controleert niet of de categorie bestaat',
                'hint' => 'Bestaat die categorie eigenlijk wel?',
                'explanation' => '`integer` controleert alleen het type, niet of de categorie bestaat. Gebruik `exists:categories,id` zodat alleen bestaande categorieën worden geaccepteerd: `\'category_id\' => [\'required\', \'exists:categories,id\']`.',
            ],
        ];

        $exercise->errors()->delete();
        $exercise->errors()->createMany($errors);
    }
}
