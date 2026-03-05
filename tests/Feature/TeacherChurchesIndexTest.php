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

it('lists only unlinked users enrolled in trainings where the teacher is titular', function (): void {
    $teacher = createTeacherUser();
    $otherTeacher = createTeacherUser();

    $training = createTeacherTraining($teacher);
    $otherTraining = createTeacherTraining($otherTeacher);

    $unlinkedStudent = User::factory()->create([
        'name' => 'Aluno Sem Igreja Listado',
        'email' => 'aluno.sem.igreja@example.com',
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $linkedChurch = Church::factory()->create(['name' => 'Igreja do Aluno Vinculado']);
    $linkedStudent = User::factory()->create([
        'name' => 'Aluno Com Igreja Oculto',
        'email' => 'aluno.com.igreja@example.com',
        'church_id' => $linkedChurch->id,
    ]);

    $unlinkedFromOtherTeacher = User::factory()->create([
        'name' => 'Aluno Sem Igreja Outro Professor',
        'email' => 'aluno.outro.professor@example.com',
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $training->students()->attach([$unlinkedStudent->id, $linkedStudent->id]);
    $otherTraining->students()->attach([$unlinkedFromOtherTeacher->id]);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->assertViewHas('unlinkedUsers', function ($paginator) use ($unlinkedStudent, $linkedStudent, $unlinkedFromOtherTeacher) {
            $listedUserIds = collect($paginator->items())->pluck('id')->all();

            expect($listedUserIds)->toContain($unlinkedStudent->id);
            expect($listedUserIds)->not->toContain($linkedStudent->id);
            expect($listedUserIds)->not->toContain($unlinkedFromOtherTeacher->id);

            return true;
        });
});

it('allows teacher to remove unlinked user enrolled in own trainings', function (): void {
    $teacher = createTeacherUser();
    $training = createTeacherTraining($teacher);

    $unlinkedStudent = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $training->students()->attach($unlinkedStudent->id);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->call('removeUnlinkedUser', $unlinkedStudent->id);

    $this->assertDatabaseMissing('users', ['id' => $unlinkedStudent->id]);
});

it('allows teacher to associate church and lists only own trainings in modal', function (): void {
    $teacher = createTeacherUser();
    $otherTeacher = createTeacherUser();

    $teacherChurch = Church::factory()->create(['name' => 'Igreja Para Vinculo Professor']);
    $otherChurch = Church::factory()->create(['name' => 'Igreja Outro Professor']);

    $teacherTraining = createTeacherTraining($teacher, $teacherChurch->id);
    $otherTraining = createTeacherTraining($otherTeacher, $otherChurch->id);

    $unlinkedStudent = User::factory()->create([
        'church_id' => null,
        'church_temp_id' => null,
    ]);

    $teacherTraining->students()->attach($unlinkedStudent->id);
    $otherTraining->students()->attach($unlinkedStudent->id);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->call('openUnlinkedUserModal', $unlinkedStudent->id)
        ->assertSet('selectedUnlinkedUserId', $unlinkedStudent->id)
        ->assertViewHas('selectedUserTrainings', function ($trainings) use ($teacherTraining, $otherTraining) {
            $trainingIds = collect($trainings->all())->pluck('id')->all();

            expect($trainingIds)->toContain($teacherTraining->id);
            expect($trainingIds)->not->toContain($otherTraining->id);

            return true;
        })
        ->set('linkChurchId', $teacherChurch->id)
        ->call('associateChurchToSelectedUser')
        ->assertSet('showUnlinkedUserModal', false);

    $unlinkedStudent->refresh();

    expect($unlinkedStudent->church_id)->toBe($teacherChurch->id);
    expect($unlinkedStudent->church_temp_id)->toBeNull();
});

it('paginates churches list with 5 rows per page', function (): void {
    $teacher = createTeacherUser();
    $lastChurchId = null;
    $firstChurchId = null;

    for ($index = 1; $index <= 6; $index++) {
        $church = Church::factory()->create([
            'name' => sprintf('Igreja %02d', $index),
        ]);

        createTeacherTraining($teacher, $church->id);

        if ($index === 1) {
            $firstChurchId = $church->id;
        }

        if ($index === 6) {
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
    $secondPage->assertDontSee(sprintf('teacher-church-%d', $firstChurchId));
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

it('searches church and user dropdown by pastor city state and email', function (): void {
    $teacher = createTeacherUser();

    $church = Church::factory()->create([
        'name' => 'Igreja Filtrada Professor',
        'pastor' => 'Pr. Elias Filtro',
        'city' => 'Uberlandia',
        'state' => 'MG',
        'contact_email' => 'filtro.igreja@example.com',
    ]);

    $user = User::factory()->create([
        'name' => 'Membro Filtro Professor',
        'email' => 'membro.filtro.professor@example.com',
        'city' => 'Uberlandia',
        'state' => 'MG',
        'church_id' => $church->id,
    ]);

    createTeacherTraining($teacher, $church->id);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->set('churchSearch', 'Elias Filtro')
        ->assertSeeText($church->name)
        ->set('churchSearch', 'Uberlandia')
        ->assertSeeText($church->name)
        ->assertSeeText($user->name)
        ->set('churchSearch', 'MG')
        ->assertSeeText($church->name)
        ->assertSeeText($user->name)
        ->set('churchSearch', 'filtro.igreja@example.com')
        ->assertSeeText($church->name)
        ->set('churchSearch', 'membro.filtro.professor@example.com')
        ->assertSeeText($user->name);
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

it('sorts churches by selected table columns with the expected precedence', function (): void {
    $teacher = createTeacherUser();

    $churchA = Church::factory()->create([
        'name' => 'Alpha Church',
        'pastor' => 'Zulu Pastor',
        'contact' => 'Marina',
        'contact_email' => 'zz@example.org',
        'city' => 'Recife',
        'state' => 'SP',
    ]);

    $churchB = Church::factory()->create([
        'name' => 'Alpha Church',
        'pastor' => 'Bravo Pastor',
        'contact' => 'Carlos',
        'contact_email' => 'aa@example.org',
        'city' => 'Manaus',
        'state' => 'AM',
    ]);

    $churchC = Church::factory()->create([
        'name' => 'Beta Church',
        'pastor' => 'Alpha Pastor',
        'contact' => 'Carlos',
        'contact_email' => 'zz@example.org',
        'city' => 'Fortaleza',
        'state' => 'AM',
    ]);

    createTeacherTraining($teacher, $churchA->id);
    createTeacherTraining($teacher, $churchB->id);
    createTeacherTraining($teacher, $churchC->id);

    $memberA = User::factory()->create(['church_id' => $churchA->id]);
    $memberB1 = User::factory()->create(['church_id' => $churchB->id]);
    $memberB2 = User::factory()->create(['church_id' => $churchB->id]);
    $memberC1 = User::factory()->create(['church_id' => $churchC->id]);
    $memberC2 = User::factory()->create(['church_id' => $churchC->id]);
    $memberC3 = User::factory()->create(['church_id' => $churchC->id]);

    Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->assertViewHas('churches', function ($paginator) use ($churchB, $churchA, $churchC) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchB->id, $churchA->id, $churchC->id]);

            return true;
        })
        ->call('sortBy', 'church')
        ->assertViewHas('churches', function ($paginator) use ($churchC, $churchA, $churchB) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchC->id, $churchA->id, $churchB->id]);

            return true;
        })
        ->call('sortBy', 'contact')
        ->assertViewHas('churches', function ($paginator) use ($churchB, $churchC, $churchA) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchB->id, $churchC->id, $churchA->id]);

            return true;
        })
        ->call('sortBy', 'location')
        ->assertViewHas('churches', function ($paginator) use ($churchC, $churchB, $churchA) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchC->id, $churchB->id, $churchA->id]);

            return true;
        })
        ->call('sortBy', 'members')
        ->assertViewHas('churches', function ($paginator) use ($churchA, $churchB, $churchC) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchA->id, $churchB->id, $churchC->id]);

            return true;
        })
        ->call('sortBy', 'members')
        ->assertViewHas('churches', function ($paginator) use ($churchC, $churchB, $churchA) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchC->id, $churchB->id, $churchA->id]);

            return true;
        });

    expect($memberA->church_id)->toBe($churchA->id);
    expect($memberB1->church_id)->toBe($churchB->id);
    expect($memberB2->church_id)->toBe($churchB->id);
    expect($memberC1->church_id)->toBe($churchC->id);
    expect($memberC2->church_id)->toBe($churchC->id);
    expect($memberC3->church_id)->toBe($churchC->id);
});

it('shows total accredited members per church for leader courses and sorts by this column', function (): void {
    $teacher = createTeacherUser();
    $facilitatorRole = Role::query()->firstOrCreate(['name' => 'Facilitator']);

    $churchOne = Church::factory()->create(['name' => 'Igreja Cred 01']);
    $churchTwo = Church::factory()->create(['name' => 'Igreja Cred 02']);
    $churchThree = Church::factory()->create(['name' => 'Igreja Cred 03']);

    createTeacherTraining($teacher, $churchOne->id);
    createTeacherTraining($teacher, $churchTwo->id);
    createTeacherTraining($teacher, $churchThree->id);

    $churchOneMemberA = User::factory()->create(['church_id' => $churchOne->id]);
    $churchOneMemberB = User::factory()->create(['church_id' => $churchOne->id]);
    $churchTwoMemberA = User::factory()->create(['church_id' => $churchTwo->id]);
    $churchTwoMemberB = User::factory()->create(['church_id' => $churchTwo->id]);
    $churchTwoMemberC = User::factory()->create(['church_id' => $churchTwo->id]);

    $churchOneMemberA->roles()->syncWithoutDetaching([$facilitatorRole->id]);
    $churchTwoMemberA->roles()->syncWithoutDetaching([$facilitatorRole->id]);
    $churchTwoMemberB->roles()->syncWithoutDetaching([$facilitatorRole->id]);

    $component = Livewire::actingAs($teacher)
        ->test(ChurchIndex::class)
        ->assertSeeText('Total de credenciados')
        ->assertViewHas('churches', function ($paginator) use ($churchOne, $churchTwo, $churchThree) {
            $churches = collect($paginator->items())->keyBy('id');

            expect((int) $churches[$churchOne->id]->total_accredited_members_count)->toBe(1);
            expect((int) $churches[$churchTwo->id]->total_accredited_members_count)->toBe(2);
            expect((int) $churches[$churchThree->id]->total_accredited_members_count)->toBe(0);

            return true;
        });

    $component
        ->call('sortBy', 'accredited')
        ->assertViewHas('churches', function ($paginator) use ($churchThree, $churchOne, $churchTwo) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchThree->id, $churchOne->id, $churchTwo->id]);

            return true;
        })
        ->call('sortBy', 'accredited')
        ->assertViewHas('churches', function ($paginator) use ($churchTwo, $churchOne, $churchThree) {
            $orderedIds = collect($paginator->items())->pluck('id')->all();

            expect($orderedIds)->toBe([$churchTwo->id, $churchOne->id, $churchThree->id]);

            return true;
        });
});
