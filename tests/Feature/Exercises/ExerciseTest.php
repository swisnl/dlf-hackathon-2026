<?php

namespace Tests\Feature\Exercises;

use App\Ai\Agents\AnswerAssessor;
use App\Ai\Agents\ExerciseTutor;
use App\Enums\ExerciseMessageRole;
use App\Enums\Variant;
use App\Models\Exercise;
use App\Models\ExerciseMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class ExerciseTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_student_sees_both_variants_without_the_answer(): void
    {
        $exercise = Exercise::factory()->create();

        $this->actingAs($exercise->user)->get(route('exercises.show', $exercise))
            ->assertOk()
            ->assertSee($exercise->question)
            ->assertSee('Variant A')
            ->assertSee('Variant B')
            ->assertSee('Leg uit waarom')
            ->assertDontSee($exercise->reference_explanation);
    }

    public function test_a_student_cannot_see_someone_elses_exercise(): void
    {
        $exercise = Exercise::factory()->create();

        $this->actingAs(User::factory()->create())->get(route('exercises.show', $exercise))
            ->assertForbidden();
    }

    public function test_a_student_can_choose_a_variant_and_explain_why(): void
    {
        AnswerAssessor::fake([['score' => 9, 'feedback' => 'Sterke uitleg!']]);
        $exercise = Exercise::factory()->create(['correct_variant' => Variant::B]);

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'b',
            'explanation' => 'Omdat de array in variant B een beginwaarde krijgt.',
        ])->assertRedirect(route('exercises.show', $exercise));

        $exercise->refresh();
        $this->assertSame(Variant::B, $exercise->chosen_variant);
        $this->assertTrue($exercise->is_correct);
        $this->assertSame(9, $exercise->score);
        $this->assertSame('Sterke uitleg!', $exercise->feedback);
        $this->assertNotNull($exercise->answered_at);

        AnswerAssessor::assertPrompted(fn ($prompt) => $prompt->contains('Keuze van de student: Variant B')
            && $prompt->contains('Omdat de array in variant B een beginwaarde krijgt.'));

        $this->actingAs($exercise->user)->get(route('exercises.show', $exercise))
            ->assertOk()
            ->assertSee('Goed gekozen!')
            ->assertSee('Sterke uitleg!')
            ->assertSee($exercise->reference_explanation)
            ->assertSee('Praat erover door');
    }

    public function test_a_wrong_choice_is_stored_as_incorrect(): void
    {
        AnswerAssessor::fake([['score' => 3, 'feedback' => 'Kijk nog eens.']]);
        $exercise = Exercise::factory()->create(['correct_variant' => Variant::A]);

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'b',
            'explanation' => 'Ik denk dat B korter en dus beter is.',
        ]);

        $this->assertFalse($exercise->refresh()->is_correct);
    }

    public function test_the_score_is_clamped_between_0_and_10(): void
    {
        AnswerAssessor::fake([['score' => 42, 'feedback' => 'Top']]);
        $exercise = Exercise::factory()->create();

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'a',
            'explanation' => 'Omdat dit de juiste variant is.',
        ]);

        $this->assertSame(10, $exercise->refresh()->score);
    }

    public function test_a_choice_and_an_explanation_are_required(): void
    {
        AnswerAssessor::fake();
        $exercise = Exercise::factory()->create();

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'c',
            'explanation' => 'kort',
        ])->assertSessionHasErrors(['choice', 'explanation']);

        $this->assertNull($exercise->refresh()->answered_at);
        AnswerAssessor::assertNeverPrompted();
    }

    public function test_an_exercise_can_only_be_answered_once(): void
    {
        AnswerAssessor::fake();
        $exercise = Exercise::factory()->answered()->create();

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'b',
            'explanation' => 'Toch een andere keuze maken.',
        ])->assertForbidden();

        AnswerAssessor::assertNeverPrompted();
    }

    public function test_a_student_cannot_answer_someone_elses_exercise(): void
    {
        AnswerAssessor::fake();
        $exercise = Exercise::factory()->create();

        $this->actingAs(User::factory()->create())->post(route('exercises.answer', $exercise), [
            'choice' => 'a',
            'explanation' => 'Omdat dit de juiste variant is.',
        ])->assertNotFound();

        $this->assertNull($exercise->refresh()->answered_at);
    }

    public function test_the_answer_is_not_stored_when_the_ai_fails(): void
    {
        AnswerAssessor::fake(fn () => throw new RuntimeException('LiteLLM is down'));
        $exercise = Exercise::factory()->create();

        $this->actingAs($exercise->user)->post(route('exercises.answer', $exercise), [
            'choice' => 'a',
            'explanation' => 'Omdat dit de juiste variant is.',
        ])->assertSessionHas('error');

        $this->assertNull($exercise->refresh()->answered_at);
    }

    public function test_a_student_can_react_and_gets_a_reply(): void
    {
        ExerciseTutor::fake(['Goede vraag! Wat gebeurt er als $items leeg is?']);
        $exercise = Exercise::factory()->answered()->create();
        ExerciseMessage::factory()->for($exercise)->create(['role' => ExerciseMessageRole::Student, 'content' => 'Eerste vraag']);
        ExerciseMessage::factory()->for($exercise)->create(['role' => ExerciseMessageRole::Tutor, 'content' => 'Eerste antwoord']);

        $this->actingAs($exercise->user)->post(route('exercises.messages.store', $exercise), [
            'content' => 'Waarom moet de array een beginwaarde hebben?',
        ])->assertRedirect(route('exercises.show', $exercise).'#gesprek');

        $messages = $exercise->messages()->get();
        $this->assertCount(4, $messages);
        $this->assertSame(ExerciseMessageRole::Student, $messages[2]->role);
        $this->assertSame('Waarom moet de array een beginwaarde hebben?', $messages[2]->content);
        $this->assertSame(ExerciseMessageRole::Tutor, $messages[3]->role);
        $this->assertSame('Goede vraag! Wat gebeurt er als $items leeg is?', $messages[3]->content);

        ExerciseTutor::assertPrompted('Waarom moet de array een beginwaarde hebben?');

        $this->actingAs($exercise->user)->get(route('exercises.show', $exercise))
            ->assertSee('Wat gebeurt er als $items leeg is?');
    }

    public function test_the_tutor_gets_the_earlier_conversation_as_history(): void
    {
        $exercise = Exercise::factory()->answered()->create();
        $exercise->messages()->create(['role' => ExerciseMessageRole::Student, 'content' => 'Eerste vraag']);
        $exercise->messages()->create(['role' => ExerciseMessageRole::Tutor, 'content' => 'Eerste antwoord']);
        $exercise->messages()->create(['role' => ExerciseMessageRole::Student, 'content' => 'Nieuwe vraag']);

        $history = collect((new ExerciseTutor($exercise))->messages());

        $this->assertSame(['Eerste vraag', 'Eerste antwoord'], $history->pluck('content')->all());
        $this->assertSame(['user', 'assistant'], $history->map(fn ($message) => $message->role->value)->all());
    }

    public function test_a_student_cannot_react_before_answering(): void
    {
        ExerciseTutor::fake();
        $exercise = Exercise::factory()->create();

        $this->actingAs($exercise->user)->post(route('exercises.messages.store', $exercise), [
            'content' => 'Mag ik een hint?',
        ])->assertForbidden();

        $this->assertDatabaseEmpty('exercise_messages');
    }

    public function test_a_student_cannot_react_on_someone_elses_exercise(): void
    {
        ExerciseTutor::fake();
        $exercise = Exercise::factory()->answered()->create();

        $this->actingAs(User::factory()->create())->post(route('exercises.messages.store', $exercise), [
            'content' => 'Hallo',
        ])->assertNotFound();

        $this->assertDatabaseEmpty('exercise_messages');
    }
}
