<?php

use App\Livewire\Pages\App\Teacher\Church\CreateModal;
use App\Livewire\Pages\App\Teacher\Church\Index as ChurchIndex;
use App\Models\Church;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createTeacherUser(?int $churchId = null): User
{
    $teacher = User::factory()->create([
        'church_id' => $churchId,
    ]);

    $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);
    $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);

    return $teacher;
}

function createTeacherTraining(User $teacher, ?int $churchId = null): Training
{
    return Training::query()->create([
        'teacher_id' => $teacher->id,
        'church_id' => $churchId,
        'status' => 0,
    ]);
}

it('lists only churches related to the teachers trainings including own church', function (): void {
    $teacherChurch = Church::factory()->create(['name' => 'Igreja do Professor']);
    $trainingChurch = Church::factory()->create(['name' => 'Igreja do Treinamento']);
    $studentChurch = Church::factory()->create(['name' => 'Igreja dos Alunos']);
    $unrelatedChurch = Church::factory()->create(['name' => 'Igreja Sem Vínculo']);

    $teacher = createTeacherUser($teacherChurch->id);

    $training = createTeacherTraining($teacher, $trainingChurch->id);

    $student = User::factory()->create([
        'church_id' => $studentChurch->id,
    ]);

    $training->students()->attach($student->id);

    $otherTeacher = createTeacherUser();
    createTeacherTraining($otherTeacher, $unrelatedChurch->id);

    $response = $this->actingAs($teacher)->get(route('app.teacher.churches.index'));

    $response->assertOk();
    $response->assertSeeText('Igreja do Professor');
    $response->assertSeeText('Igreja do Treinamento');
    $response->assertSeeText('Igreja dos Alunos');
    $response->assertDontSeeText('Igreja Sem Vínculo');
});

it('paginates churches list with 15 rows per page', function (): void {
    $teacher = createTeacherUser();
    $lastChurchId = null;

    for ($index = 1; $index <= 16; $index++) {
        $church = Church::factory()->create([
            'name' => sprintf('Igreja %02d', $index),
        ]);

        createTeacherTraining($teacher, $church->id);

        if ($index === 16) {
            $lastChurchId = $church->id;
        }
    }

    $firstPage = $this->actingAs($teacher)->get(route('app.teacher.churches.index'));

    $firstPage->assertOk();
    $firstPage->assertSeeText('Igreja 01');
    $firstPage->assertDontSee(sprintf('teacher-church-%d', $lastChurchId));

    $secondPage = $this->actingAs($teacher)->get(route('app.teacher.churches.index', ['page' => 2]));

    $secondPage->assertOk();
    $secondPage->assertSee(sprintf('teacher-church-%d', $lastChurchId));
    $secondPage->assertDontSeeText('Igreja 01');
});

it('allows teacher to remove a related church', function (): void {
    $teacher = createTeacherUser();

    $church = Church::factory()->create(['name' => 'Igreja Removível']);

    createTeacherTraining($teacher, $church->id);

    $this->assertDatabaseHas('churches', ['id' => $church->id]);

    $response = $this->actingAs($teacher)->delete(route('app.teacher.churches.destroy', $church));

    $response->assertRedirect(route('app.teacher.churches.index'));
    $this->assertDatabaseMissing('churches', ['id' => $church->id]);
});

it('forbids teacher from removing own church', function (): void {
    $church = Church::factory()->create(['name' => 'Igreja do Próprio Professor']);
    $teacher = createTeacherUser($church->id);

    $response = $this->actingAs($teacher)->delete(route('app.teacher.churches.destroy', $church));

    $response->assertForbidden();
    $this->assertDatabaseHas('churches', ['id' => $church->id]);
});

it('shows church and user results in search dropdown without filtering table rows', function (): void {
    $teacher = createTeacherUser();

    $churchOne = Church::factory()->create(['name' => 'Igreja Esperança']);
    $churchTwo = Church::factory()->create(['name' => 'Igreja Vitória']);
    $matchingUser = User::factory()->create([
        'name' => 'Carlos Esperança',
        'email' => 'carlos.esperanca@example.com',
        'church_id' => $churchOne->id,
    ]);

    createTeacherTraining($teacher, $churchOne->id);
    createTeacherTraining($teacher, $churchTwo->id);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->set('churchSearch', 'Esperança')
        ->assertSeeText('Igrejas encontradas')
        ->assertSeeText('Igreja Esperança')
        ->assertSeeText('Usuários encontrados')
        ->assertSeeText($matchingUser->name)
        ->assertSee(route('app.teacher.churches.show', $churchOne))
        ->assertSeeText('Igreja Vitória');
});

it('shows not found messages in search dropdown when there are no matches', function (): void {
    $teacher = createTeacherUser();
    $church = Church::factory()->create(['name' => 'Igreja Base']);

    createTeacherTraining($teacher, $church->id);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->set('churchSearch', 'Termo Inexistente XYZ')
        ->assertSeeText('Igreja não encontrada.')
        ->assertSeeText('Nenhum usuário encontrado para este termo.');
});

it('renders row navigation link for each listed church', function (): void {
    $teacher = createTeacherUser();
    $church = Church::factory()->create(['name' => 'Igreja Linha Clicável']);

    createTeacherTraining($teacher, $church->id);

    $response = $this->actingAs($teacher)->get(route('app.teacher.churches.index'));

    $response->assertOk();
    $response->assertSee('data-row-link="'.route('app.teacher.churches.show', $church).'"', false);
});

it('creates a new church from modal and relates it to the teacher', function (): void {
    Storage::fake('public');

    $teacher = createTeacherUser();
    $logo = UploadedFile::fake()->image('logo.png', 200, 200);

    Livewire::actingAs($teacher)
        ->test(CreateModal::class)
        ->call('openModal')
        ->set('logoUpload', $logo)
        ->set('church_name', 'Igreja Nova Aliança')
        ->set('pastor_name', 'Pr. Daniel')
        ->set('phone_church', '11999998888')
        ->set('church_email', 'contato@igrejanova.org')
        ->set('church_contact', 'Maria Oliveira')
        ->set('church_contact_phone', '11988887777')
        ->set('church_contact_email', 'maria@igrejanova.org')
        ->set('church_notes', 'Observações de teste')
        ->set('churchAddress.postal_code', '70000000')
        ->set('churchAddress.street', 'Rua Central')
        ->set('churchAddress.number', '123')
        ->set('churchAddress.complement', 'Sala 2')
        ->set('churchAddress.district', 'Centro')
        ->set('churchAddress.city', 'Brasilia')
        ->set('churchAddress.state', 'df')
        ->call('save')
        ->assertDispatched('teacher-church-created')
        ->assertSet('showModal', false);

    $createdChurch = Church::query()->where('name', 'Igreja Nova Aliança')->first();

    expect($createdChurch)->not->toBeNull();
    expect($createdChurch->missionaries()->where('users.id', $teacher->id)->exists())->toBeTrue();
    expect($createdChurch->logo)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $createdChurch->logo);
});
