<?php

namespace Tests\Feature\Filament;

use App\Enums\UserRole;
use App\Filament\Resources\Students\Pages\ListStudents;
use App\Filament\Resources\Students\Pages\ViewStudent;
use App\Filament\Resources\Students\RelationManagers\AttemptsRelationManager;
use App\Filament\Resources\Students\StudentResource;
use App\Models\Attempt;
use App\Models\Badge;
use App\Models\Exercise;
use App\Models\Student;
use App\Models\User;
use Filament\Actions\DeleteAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class StudentResourceTest extends TestCase
{
    use RefreshDatabase;

    #[DataProvider('panelRoles')]
    public function test_teachers_and_admins_can_view_students(UserRole $role): void
    {
        $student = Student::query()->findOrFail(User::factory()->create()->id);

        $this->actingAs(User::factory()->create(['role' => $role]));

        $this->get(StudentResource::getUrl('index'))->assertOk();
        $this->get(StudentResource::getUrl('view', ['record' => $student]))->assertOk();
    }

    /**
     * @return array<string, array{UserRole}>
     */
    public static function panelRoles(): array
    {
        return [
            'teacher' => [UserRole::Teacher],
            'admin' => [UserRole::Admin],
        ];
    }

    public function test_only_students_are_listed(): void
    {
        $students = Student::query()->findMany(User::factory()->count(2)->create()->modelKeys());
        $teacher = User::factory()->teacher()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($teacher);

        Livewire::test(ListStudents::class)
            ->assertCanSeeTableRecords($students)
            ->assertCanNotSeeTableRecords(Student::withoutGlobalScopes()->findMany([$teacher->id, $admin->id]));
    }

    public function test_teachers_cannot_view_a_teacher_as_a_student(): void
    {
        $this->actingAs(User::factory()->teacher()->create());

        $this->get(StudentResource::getUrl('view', ['record' => User::factory()->teacher()->create()]))->assertNotFound();
    }

    public function test_the_list_shows_stars_badges_and_attempted_exercises(): void
    {
        $user = User::factory()->create(['stars' => 12]);
        $user->badges()->attach(Badge::factory()->count(2)->create(), ['awarded_at' => now()]);
        $exercises = Exercise::factory()->count(2)->create();
        Attempt::factory()->for($user)->for($exercises[0])->create();
        Attempt::factory()->for($user)->for($exercises[0])->retry()->create();
        Attempt::factory()->for($user)->for($exercises[1])->create();

        $this->actingAs(User::factory()->teacher()->create());

        $student = Student::query()->findOrFail($user->id);

        Livewire::test(ListStudents::class)
            ->assertTableColumnStateSet('stars', 12, $student)
            ->assertTableColumnStateSet('badges_count', 2, $student)
            ->assertTableColumnStateSet('exercises_count', 2, $student);
    }

    public function test_the_detail_page_lists_the_attempts(): void
    {
        $student = Student::query()->findOrFail(User::factory()->create()->id);
        $attempts = Attempt::factory()->for($student, 'user')->count(2)->create();

        $this->actingAs(User::factory()->teacher()->create());

        Livewire::test(AttemptsRelationManager::class, [
            'ownerRecord' => $student,
            'pageClass' => ViewStudent::class,
        ])
            ->assertOk()
            ->assertCanSeeTableRecords($attempts);
    }

    public function test_teachers_can_delete_a_student_and_their_progress(): void
    {
        $student = Student::query()->findOrFail(User::factory()->create()->id);
        Attempt::factory()->for($student, 'user')->create();
        $student->badges()->attach(Badge::factory()->create(), ['awarded_at' => now()]);

        $this->actingAs(User::factory()->teacher()->create());

        Livewire::test(ViewStudent::class, ['record' => $student->getRouteKey()])
            ->callAction(DeleteAction::class);

        $this->assertModelMissing($student);
        $this->assertDatabaseMissing('attempts', ['user_id' => $student->id]);
        $this->assertDatabaseMissing('badge_user', ['user_id' => $student->id]);
    }

    public function test_students_cannot_access_the_student_resource(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(StudentResource::getUrl('index'))->assertForbidden();
    }
}
