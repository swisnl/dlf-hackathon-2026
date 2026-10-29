<?php

namespace Tests\Feature\Exercises;

use App\Ai\Agents\ExerciseGenerator;
use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use RuntimeException;
use Tests\TestCase;

class SubmissionTest extends TestCase
{
    use RefreshDatabase;

    private const array GENERATED = [
        'concept' => 'Constructor',
        'question' => 'Welke constructor is juist?',
        'correct_code' => 'public function __construct(private int $total = 0) {}',
        'incorrect_code' => 'public function __construct(private int $total = "0") {}',
        'reference_explanation' => 'Een int kan geen string als standaardwaarde hebben.',
    ];

    public function test_a_student_sees_the_upload_form_and_only_their_own_submissions(): void
    {
        $user = User::factory()->create();
        Submission::factory()->for($user)->create(['title' => 'Mijn winkelmandje']);
        Submission::factory()->create(['title' => 'Code van iemand anders']);

        $this->actingAs($user)->get(route('submissions.index'))
            ->assertOk()
            ->assertSee('Upload je code')
            ->assertSee('Mijn winkelmandje')
            ->assertDontSee('Code van iemand anders');
    }

    public function test_uploading_a_file_stores_it_and_generates_an_exercise(): void
    {
        ExerciseGenerator::fake([self::GENERATED]);
        $user = User::factory()->create();
        $file = UploadedFile::fake()->createWithContent('Cart.php', "<?php\nclass Cart {}\n");

        $response = $this->actingAs($user)->post(route('submissions.store'), [
            'title' => 'Opdracht 3',
            'file' => $file,
        ]);

        $submission = Submission::query()->sole();
        $exercise = Exercise::query()->sole();
        $response->assertRedirect(route('exercises.show', $exercise));

        $this->assertTrue($submission->user->is($user));
        $this->assertSame('Cart.php', $submission->filename);
        $this->assertSame("<?php\nclass Cart {}\n", $submission->code);

        $this->assertTrue($exercise->user->is($user));
        $this->assertTrue($exercise->submission->is($submission));
        $this->assertSame('Constructor', $exercise->concept);
        $this->assertSame(self::GENERATED['correct_code'], $exercise->variant($exercise->correct_variant));
        $this->assertSame(self::GENERATED['incorrect_code'], $exercise->variant($exercise->correct_variant === Variant::A ? Variant::B : Variant::A));
        $this->assertNull($exercise->chosen_variant);

        ExerciseGenerator::assertPrompted(fn ($prompt) => $prompt->contains('class Cart {}'));
    }

    public function test_pasted_code_can_be_submitted_without_a_file(): void
    {
        ExerciseGenerator::fake([self::GENERATED]);

        $this->actingAs(User::factory()->create())->post(route('submissions.store'), [
            'title' => 'Geplakt',
            'code' => 'print("hallo")',
        ])->assertRedirect();

        $submission = Submission::query()->sole();
        $this->assertNull($submission->filename);
        $this->assertSame('print("hallo")', $submission->code);
    }

    public function test_a_title_and_code_are_required(): void
    {
        ExerciseGenerator::fake();

        $this->actingAs(User::factory()->create())->post(route('submissions.store'), [])
            ->assertSessionHasErrors(['title', 'file', 'code']);

        $this->assertDatabaseEmpty('submissions');
        ExerciseGenerator::assertNeverPrompted();
    }

    public function test_files_that_are_not_code_are_rejected(): void
    {
        ExerciseGenerator::fake();

        $this->actingAs(User::factory()->create())->post(route('submissions.store'), [
            'title' => 'Foto',
            'file' => UploadedFile::fake()->image('foto.png'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseEmpty('submissions');
    }

    public function test_binary_content_is_rejected(): void
    {
        ExerciseGenerator::fake();

        $this->actingAs(User::factory()->create())->post(route('submissions.store'), [
            'title' => 'Binair',
            'file' => UploadedFile::fake()->createWithContent('data.txt', "\xFF\xFE\x00\x81"),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseEmpty('submissions');
    }

    public function test_files_larger_than_200_kb_are_rejected(): void
    {
        ExerciseGenerator::fake();

        $this->actingAs(User::factory()->create())->post(route('submissions.store'), [
            'title' => 'Groot',
            'file' => UploadedFile::fake()->create('groot.php', 201),
        ])->assertSessionHasErrors('file');
    }

    public function test_the_submission_is_kept_when_the_ai_fails(): void
    {
        ExerciseGenerator::fake(fn () => throw new RuntimeException('LiteLLM is down'));

        $response = $this->actingAs(User::factory()->create())->post(route('submissions.store'), [
            'title' => 'Opdracht 3',
            'code' => 'echo 1;',
        ]);

        $submission = Submission::query()->sole();
        $response->assertRedirect(route('submissions.show', $submission))->assertSessionHas('error');
        $this->assertDatabaseEmpty('exercises');
    }

    public function test_a_student_can_view_their_submission_but_not_someone_elses(): void
    {
        $submission = Submission::factory()->create(['title' => 'Mijn code']);
        Exercise::factory()->for($submission)->create(['concept' => 'Loops']);

        $this->actingAs($submission->user)->get(route('submissions.show', $submission))
            ->assertOk()
            ->assertSee('Mijn code')
            ->assertSee('Loops');

        $this->actingAs(User::factory()->create())->get(route('submissions.show', $submission))
            ->assertForbidden();
    }

    public function test_a_student_can_request_another_exercise_with_a_new_concept(): void
    {
        ExerciseGenerator::fake([self::GENERATED]);
        $submission = Submission::factory()->create();
        Exercise::factory()->for($submission)->create(['concept' => 'Loops']);

        $this->actingAs($submission->user)->post(route('exercises.store', $submission))
            ->assertRedirect();

        $this->assertSame(2, $submission->exercises()->count());
        ExerciseGenerator::assertPrompted(fn ($prompt) => $prompt->contains('Kies een ander concept dan deze eerdere oefeningen: Loops.'));
    }

    public function test_a_student_cannot_request_exercises_for_someone_elses_code(): void
    {
        ExerciseGenerator::fake();
        $submission = Submission::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('exercises.store', $submission))
            ->assertForbidden();

        ExerciseGenerator::assertNeverPrompted();
    }
}
