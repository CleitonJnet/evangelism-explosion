<?php

use App\Models\Church;
use App\Models\Course;
use App\Models\Inventory;
use App\Models\Material;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

function createBasePortalUser(array $roles = ['Teacher']): User
{
    $church = Church::factory()->create();
    $user = User::factory()->create(['church_id' => $church->id]);

    $roleIds = collect($roles)
        ->map(fn (string $roleName): int => Role::query()->firstOrCreate(['name' => $roleName])->id);

    $user->roles()->syncWithoutDetaching($roleIds->all());
    $user->load('church');

    return $user;
}

function createServingTraining(User $user, array $attributes = []): Training
{
    $training = Training::factory()->create(array_merge([
        'teacher_id' => $user->id,
        'church_id' => $user->church_id,
        'notes' => null,
        'status' => TrainingStatus::Scheduled,
        'city' => 'Campinas',
        'state' => 'SP',
    ], collect($attributes)->except(['mentor_user', 'assistant_user', 'date'])->all()));

    if (isset($attributes['mentor_user'])) {
        $training->mentors()->attach($attributes['mentor_user']->id, ['created_by' => $user->id]);
    }

    if (isset($attributes['assistant_user'])) {
        $training->assistantTeachers()->attach($attributes['assistant_user']->id);
    }

    if (isset($attributes['date'])) {
        DB::table('event_dates')->where('training_id', $training->id)->delete();
        DB::table('event_dates')->insert([
            'training_id' => $training->id,
            'date' => $attributes['date'],
            'start_time' => '09:00:00',
            'end_time' => '17:00:00',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    return $training;
}

function createBaseInventoryViewData(User $user): Inventory
{
    $inventory = Inventory::query()->create([
        'name' => 'Acervo Base Principal',
        'kind' => 'base',
        'church_id' => $user->church_id,
        'is_active' => true,
        'city' => 'Campinas',
        'state' => 'SP',
    ]);

    $manual = Material::query()->create([
        'name' => 'Manual da Base',
        'type' => 'simple',
        'price' => '12,00',
        'minimum_stock' => 3,
        'is_active' => true,
    ]);

    $inventory->materials()->attach($manual->id, [
        'received_items' => 8,
        'current_quantity' => 1,
        'lost_items' => 2,
    ]);

    return $inventory;
}

function createInventoryAlert(User $user): Inventory
{
    $inventory = Inventory::query()->create([
        'name' => 'Acervo Base Centro',
        'kind' => 'teacher',
        'user_id' => $user->id,
        'is_active' => true,
    ]);

    $material = Material::query()->create([
        'name' => 'Kit Aula',
        'type' => 'simple',
        'price' => '10,00',
        'minimum_stock' => 5,
        'is_active' => true,
    ]);

    DB::table('inventory_material')->insert([
        'inventory_id' => $inventory->id,
        'material_id' => $material->id,
        'received_items' => 2,
        'current_quantity' => 1,
        'lost_items' => 0,
    ]);

    return $inventory;
}

it('shows the base portal dashboard grouped by operational context instead of isolated roles', function () {
    Carbon::setTestNow('2026-03-13 10:00:00');

    $user = createBasePortalUser(['Teacher', 'Mentor']);
    $inventory = createInventoryAlert($user);
    $upcomingTraining = createServingTraining($user, ['date' => '2026-03-20']);
    createServingTraining($user, ['date' => '2026-03-01', 'notes' => null]);

    $response = $this
        ->actingAs($user)
        ->get(route('app.portal.base.dashboard'));

    $response->assertOk();
    $response->assertSee('Portal Base');
    $response->assertSee('Minha Base');
    $response->assertSee('Treinamentos em que Sirvo');
    $response->assertSee('Eventos da Base');
    $response->assertSee('Programacao pendente');
    $response->assertSee('Relatorios pendentes');
    $response->assertSee('Alertas de acervo');
    $response->assertSee(route('app.portal.base.my-base'), false);
    $response->assertSee(route('app.portal.base.serving'), false);
    $response->assertSee(route('app.portal.base.events'), false);
    $response->assertSee(route('app.teacher.inventory.show', $inventory), false);
    $response->assertSee(route('app.portal.base.trainings.show', $upcomingTraining), false);
});

it('reuses the shared training index on the base portal serving page with serving filters', function () {
    $user = createBasePortalUser(['Teacher', 'Mentor']);
    $otherTeacher = createBasePortalUser(['Teacher']);

    $course = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clinica',
        'name' => 'Treinamento Multi Base',
    ]);
    $otherCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Workshop',
        'name' => 'Treinamento Oculto',
    ]);
    $matchingChurch = Church::factory()->create([
        'name' => 'Igreja Base Campinas',
        'city' => 'Campinas',
        'state' => 'SP',
    ]);
    $hiddenChurch = Church::factory()->create([
        'name' => 'Igreja Base Recife',
        'city' => 'Recife',
        'state' => 'PE',
    ]);

    createServingTraining($user, [
        'course_id' => $course->id,
        'church_id' => $matchingChurch->id,
        'date' => '2026-05-10',
    ]);
    createServingTraining($user, [
        'course_id' => $course->id,
        'church_id' => $matchingChurch->id,
        'date' => '2026-05-11',
        'teacher_id' => $otherTeacher->id,
        'assistant_user' => $user,
    ]);
    $mentoredTraining = createServingTraining($user, [
        'course_id' => $course->id,
        'church_id' => $matchingChurch->id,
        'date' => '2026-05-12',
        'teacher_id' => $otherTeacher->id,
        'mentor_user' => $user,
    ]);
    $hiddenTraining = createServingTraining($user, [
        'course_id' => $otherCourse->id,
        'church_id' => $hiddenChurch->id,
        'date' => '2026-06-18',
        'teacher_id' => $otherTeacher->id,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('app.portal.base.serving.scheduled', [
            'assignment' => 'mentor',
            'church' => 'Campinas',
            'from' => '2026-05-01',
            'to' => '2026-05-31',
        ]));

    $response->assertOk();
    $response->assertSee('Treinamentos em que Sirvo');
    $response->assertSee('name="assignment"', false);
    $response->assertSee('name="church"', false);
    $response->assertSee('name="from"', false);
    $response->assertSee('name="to"', false);
    $response->assertSee('Treinamento Multi Base');
    $response->assertDontSee('Treinamento Oculto');
    $response->assertSee(route('app.portal.base.trainings.context', $mentoredTraining), false);
    $response->assertDontSee(route('app.portal.base.trainings.context', $hiddenTraining), false);
});

it('shows hosted base events separately from trainings served outside the base', function () {
    $user = createBasePortalUser(['Teacher', 'Mentor']);
    $otherTeacher = createBasePortalUser(['Teacher']);

    $hostedCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clinica',
        'name' => 'Evento Sediado da Base',
    ]);
    $outsideCourse = Course::factory()->create([
        'execution' => 0,
        'type' => 'Workshop',
        'name' => 'Evento Fora da Base',
    ]);

    $hostedTraining = createServingTraining($user, [
        'course_id' => $hostedCourse->id,
        'date' => '2026-06-10',
    ]);

    $outsideTraining = createServingTraining($user, [
        'course_id' => $outsideCourse->id,
        'church_id' => Church::factory()->create([
            'name' => 'Igreja Base Recife',
            'city' => 'Recife',
            'state' => 'PE',
        ])->id,
        'teacher_id' => $otherTeacher->id,
        'assistant_user' => $user,
        'date' => '2026-06-14',
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('app.portal.base.events'));

    $response->assertOk();
    $response->assertSee('Eventos sediados pela minha base');
    $response->assertSee('Quando eu sirvo fora da minha base');
    $response->assertSee('Subareas da operacao local');
    $response->assertSee('Evento Sediado da Base');
    $response->assertSee('Evento da minha base');
    $response->assertSee(route('app.portal.base.trainings.show', $hostedTraining), false);
    $response->assertSee('Evento Fora da Base');
    $response->assertSee('Sirvo fora da minha base');
    $response->assertSee(route('app.portal.base.trainings.show', $outsideTraining), false);
});

it('blocks portal base event pages for trainings outside the user context', function () {
    $user = createBasePortalUser(['Teacher', 'Director']);
    $otherTeacher = createBasePortalUser(['Teacher']);
    $training = createServingTraining($user, [
        'teacher_id' => $otherTeacher->id,
        'church_id' => Church::factory()->create()->id,
    ]);

    $this->actingAs($user)
        ->get(route('app.portal.base.trainings.show', $training))
        ->assertForbidden();
});

it('shows filtered tabs and local operation areas for a teacher in the base portal event wrapper', function () {
    $teacher = createBasePortalUser(['Teacher']);
    $training = createServingTraining($teacher, ['date' => '2026-04-20']);

    $response = $this->actingAs($teacher)
        ->get(route('app.portal.base.trainings.show', $training));

    $response->assertOk();
    $response->assertSee('Portal Base e Treinamentos');
    $response->assertSee('Evento sediado pela sua base e vinculado a sua atuacao');
    $response->assertSee('Professor titular');
    $response->assertSee('Inscricoes locais');
    $response->assertSee('Preparacao local');
    $response->assertSee('Materiais de apoio');
    $response->assertSee('Relatorios da igreja');
    $response->assertSee(route('app.portal.base.trainings.registrations', $training), false);
    $response->assertSee(route('app.portal.base.trainings.preparation', $training), false);
    $response->assertSee(route('app.portal.base.trainings.schedule', $training), false);
    $response->assertSee(route('app.portal.base.trainings.materials', $training), false);
    $response->assertSee(route('app.portal.base.trainings.statistics', $training), false);
    $response->assertSee(route('app.portal.base.trainings.stp.approaches', $training), false);
});

it('shows a read only base event view for a facilitator from the host church', function () {
    $facilitator = createBasePortalUser(['Facilitator']);
    $training = createServingTraining($facilitator, [
        'teacher_id' => User::factory()->create()->id,
        'church_id' => $facilitator->church_id,
        'date' => '2026-04-22',
    ]);

    $response = $this->actingAs($facilitator)
        ->get(route('app.portal.base.trainings.show', $training));

    $response->assertOk();
    $response->assertSee('Evento sediado pela sua igreja-base');
    $response->assertSee('Facilitador');
    $response->assertSee(route('app.portal.base.trainings.preparation', $training), false);
    $response->assertSee(route('app.portal.base.trainings.schedule', $training), false);
    $response->assertSee(route('app.portal.base.trainings.materials', $training), false);
    $response->assertDontSee(route('app.portal.base.trainings.registrations', $training), false);
    $response->assertDontSee(route('app.portal.base.trainings.statistics', $training), false);

    $this->actingAs($facilitator)
        ->get(route('app.portal.base.trainings.registrations', $training))
        ->assertForbidden();

    $this->actingAs($facilitator)
        ->get(route('app.portal.base.trainings.preparation', $training))
        ->assertOk();

    $this->actingAs($facilitator)
        ->get(route('app.portal.base.trainings.materials', $training))
        ->assertOk();
});

it('keeps the legacy context route working by redirecting to the new base event wrapper', function () {
    $mentor = createBasePortalUser(['Mentor']);
    $training = createServingTraining($mentor, [
        'teacher_id' => User::factory()->create()->id,
        'mentor_user' => $mentor,
    ]);

    $this->actingAs($mentor)
        ->get(route('app.portal.base.trainings.context', $training))
        ->assertRedirect(route('app.portal.base.trainings.show', $training));
});

it('filters top-level base portal navigation for users without serving capabilities', function () {
    $facilitator = createBasePortalUser(['Facilitator']);
    $hostedTraining = createServingTraining($facilitator, [
        'teacher_id' => User::factory()->create()->id,
        'church_id' => $facilitator->church_id,
        'date' => '2026-05-15',
    ]);

    $response = $this->actingAs($facilitator)
        ->get(route('app.portal.base.dashboard'));

    $response->assertOk();
    $response->assertSee('Minha Base');
    $response->assertSee('Eventos da Base');
    $response->assertDontSee(route('app.portal.base.serving'), false);
    $response->assertSee(route('app.portal.base.my-base'), false);
    $response->assertSee(route('app.portal.base.events'), false);
    $response->assertSee(route('app.portal.base.trainings.show', $hostedTraining), false);

    $this->actingAs($facilitator)
        ->get(route('app.portal.base.serving'))
        ->assertForbidden();
});

it('shows the base inventory page filtered by the current base church', function () {
    $fieldworker = createBasePortalUser(['FieldWorker']);
    $inventory = createBaseInventoryViewData($fieldworker);
    $otherChurch = Church::factory()->create(['name' => 'Outra Base']);

    Inventory::query()->create([
        'name' => 'Acervo de Outra Base',
        'kind' => 'base',
        'church_id' => $otherChurch->id,
        'is_active' => true,
    ]);

    $course = Course::factory()->create([
        'execution' => 0,
        'type' => 'Clinica',
        'name' => 'Treinamento Base',
    ]);

    $manual = Material::query()->where('name', 'Manual da Base')->firstOrFail();
    $course->materials()->attach($manual->id);

    $training = createServingTraining($fieldworker, [
        'course_id' => $course->id,
        'date' => '2026-04-10',
    ]);

    DB::table('stock_movements')->insert([
        [
            'inventory_id' => $inventory->id,
            'material_id' => $manual->id,
            'user_id' => $fieldworker->id,
            'training_id' => null,
            'movement_type' => 'entry',
            'quantity' => 8,
            'balance_after' => 8,
            'notes' => 'Recebido do estoque central',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ],
        [
            'inventory_id' => $inventory->id,
            'material_id' => $manual->id,
            'user_id' => $fieldworker->id,
            'training_id' => $training->id,
            'movement_type' => 'exit',
            'quantity' => 2,
            'balance_after' => 1,
            'notes' => 'Uso no evento',
            'created_at' => now(),
            'updated_at' => now(),
        ],
    ]);

    $response = $this
        ->actingAs($fieldworker)
        ->get(route('app.portal.base.inventory'));

    $response->assertOk();
    $response->assertSee('Acervo da Base');
    $response->assertSee('Acervo Base Principal');
    $response->assertDontSee('Acervo de Outra Base');
    $response->assertSee('Manual da Base');
    $response->assertSee('Historico de entradas relevantes');
    $response->assertSee('Uso por evento');
    $response->assertSee('Necessidades para proximos eventos');
    $response->assertSee(route('app.portal.base.trainings.show', $training), false);
});

it('blocks teachers without institutional base capability from the base inventory page', function () {
    $teacher = createBasePortalUser(['Teacher']);

    $this->actingAs($teacher)
        ->get(route('app.portal.base.inventory'))
        ->assertForbidden();
});
