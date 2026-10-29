<?php

namespace Tests\Feature\Exercises;

use App\Models\Exercise;
use App\Models\Submission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class StudentAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_the_login_page(): void
    {
        $exercise = Exercise::factory()->forSubmission()->create();

        foreach ([route('submissions.index'), route('submissions.show', $exercise->submission_id), route('exercises.show', $exercise)] as $url) {
            $this->get($url)->assertRedirect(route('login'));
        }
    }

    public function test_students_must_verify_their_email_first(): void
    {
        $submission = Submission::factory()->for(User::factory()->unverified())->create();

        $this->actingAs($submission->user)->get(route('submissions.index'))->assertRedirect(route('verification.notice'));
        $this->actingAs($submission->user)->post(route('submissions.store'), ['title' => 'Opdracht', 'code' => 'echo 1;'])
            ->assertRedirect(route('verification.notice'));
    }

    public function test_after_logging_in_a_student_lands_on_the_start_of_the_student_pages(): void
    {
        $student = User::factory()->create(['name' => 'Sam de Vries']);

        $this->post('/login', ['email' => $student->email, 'password' => 'password'])
            ->assertRedirect(route('dashboard'));

        $this->get(route('dashboard'))->assertInertia(fn (Assert $page) => $page->component('Dashboard')
            ->where('auth.user.name', 'Sam de Vries'));
    }
}
