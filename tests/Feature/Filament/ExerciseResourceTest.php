<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Exercises\ExerciseResource;
use App\Filament\Resources\Exercises\Pages\CreateExercise;
use App\Filament\Resources\Exercises\Pages\EditExercise;
use App\Filament\Resources\Exercises\Pages\ListExercises;
use App\Models\Attempt;
use App\Models\Exercise;
use App\Models\ExerciseError;
use App\Models\Topic;
use App\Models\User;
use Closure;
use Filament\Actions\Testing\TestAction;
use Filament\Forms\Components\Repeater;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ExerciseResourceTest extends TestCase
{
    use RefreshDatabase;

    private const WRONG_CODE = "<?php\n\nclass Post\n{\n    public \$guarded = [];\n}";

    private Closure $undoRepeaterFake;

    protected function setUp(): void
    {
        parent::setUp();

        $this->undoRepeaterFake = Repeater::fake();
        $this->actingAs(User::factory()->teacher()->create());
    }

    protected function tearDown(): void
    {
        ($this->undoRepeaterFake)();

        parent::tearDown();
    }

    public function test_students_cannot_access_exercises(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(ExerciseResource::getUrl('index'))->assertForbidden();
    }

    public function test_teachers_can_list_exercises(): void
    {
        $exercises = Exercise::factory()->count(2)->create();

        Livewire::test(ListExercises::class)
            ->assertOk()
            ->assertCanSeeTableRecords($exercises);
    }

    public function test_teachers_can_create_a_published_exercise_with_errors(): void
    {
        Livewire::test(CreateExercise::class)
            ->fillForm($this->validData())
            ->call('create')
            ->assertHasNoFormErrors();

        $exercise = Exercise::query()->sole();
        $this->assertNotNull($exercise->published_at);
        $this->assertSame([5], $exercise->errors()->pluck('line')->all());
    }

    public function test_an_exercise_without_errors_is_rejected(): void
    {
        Livewire::test(CreateExercise::class)
            ->fillForm([...$this->validData(), 'errors' => []])
            ->call('create')
            ->assertHasFormErrors(['errors']);
    }

    public function test_error_lines_must_exist_in_the_wrong_class(): void
    {
        Livewire::test(CreateExercise::class)
            ->fillForm([...$this->validData(), 'errors' => [$this->error(7)]])
            ->call('create')
            ->assertHasFormErrors(['errors.0.line' => 'max']);
    }

    public function test_error_lines_must_be_unique(): void
    {
        Livewire::test(CreateExercise::class)
            ->fillForm([...$this->validData(), 'errors' => [$this->error(5), $this->error(5)]])
            ->call('create')
            ->assertHasFormErrors(['errors.0.line', 'errors.1.line']);
    }

    public function test_saving_a_published_exercise_keeps_its_publication_date(): void
    {
        $exercise = Exercise::factory()->has(ExerciseError::factory()->state(['line' => 1]), 'errors')->create(['published_at' => now()->subWeek()]);

        Livewire::test(EditExercise::class, ['record' => $exercise->getRouteKey()])
            ->fillForm(['title' => 'Nieuwe titel'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($exercise->published_at->equalTo($exercise->fresh()->published_at));
    }

    public function test_teachers_can_turn_an_exercise_back_into_a_draft(): void
    {
        $exercise = Exercise::factory()->has(ExerciseError::factory()->state(['line' => 1]), 'errors')->create(['published_at' => now()]);

        Livewire::test(EditExercise::class, ['record' => $exercise->getRouteKey()])
            ->fillForm(['published_at' => false])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertNull($exercise->fresh()->published_at);
    }

    public function test_editing_an_attempted_exercise_shows_a_warning(): void
    {
        $exercise = Exercise::factory()->create();

        Livewire::test(EditExercise::class, ['record' => $exercise->getRouteKey()])
            ->assertDontSee(__('resource.exercise.warnings.attempted.heading'));

        Attempt::factory()->for($exercise)->create();

        Livewire::test(EditExercise::class, ['record' => $exercise->getRouteKey()])
            ->assertSee(__('resource.exercise.warnings.attempted.heading'));
    }

    public function test_the_preview_shows_both_classes_and_marks_the_errors(): void
    {
        $exercise = Exercise::factory()->has(ExerciseError::factory()->state(['line' => 1, 'title' => 'Verkeerde relatie']), 'errors')->create();

        Livewire::test(ListExercises::class)
            ->mountAction(TestAction::make('preview')->table($exercise))
            ->assertMountedActionModalSee(['Verkeerde relatie', 'class Post extends Model']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validData(): array
    {
        return [
            'title' => 'Mass assignment',
            'topic_id' => Topic::factory()->create()->id,
            'stars' => 2,
            'correct_code' => "<?php\n\nclass Post\n{\n    public \$fillable = ['title'];\n}",
            'wrong_code' => self::WRONG_CODE,
            'published_at' => true,
            'errors' => [$this->error(5)],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function error(int $line): array
    {
        return [
            'line' => $line,
            'title' => 'Alles is mass assignable',
            'hint' => 'Welke velden mag een gebruiker invullen?',
            'explanation' => 'Gebruik $fillable in plaats van een lege $guarded.',
        ];
    }
}
