<?php

namespace Database\Seeders;

use App\Models\Church;
use App\Models\Course;
use App\Models\EventDate;
use App\Models\Role;
use App\Models\Training;
use App\Models\User;
use App\TrainingStatus;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Seeder;

class ClinicEvangelismoEficazSeeder extends Seeder
{
    private const TRAINING_MARKER = '[SEED] Clinic EE full-flow';

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mentorRole = Role::query()->firstOrCreate(['name' => 'Mentor']);
        $studentRole = Role::query()->firstOrCreate(['name' => 'Student']);
        $teacherRole = Role::query()->firstOrCreate(['name' => 'Teacher']);

        $church = Church::query()->first() ?? Church::query()->create([
            'name' => 'Igreja Sede da Clínica',
            'city' => 'Rio de Janeiro',
            'state' => 'RJ',
        ]);

        $teacher = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'Teacher'))
            ->orderBy('id')
            ->first();

        if (! $teacher) {
            $teacher = User::query()->orderBy('id')->first() ?? User::factory()->create([
                'church_id' => $church->id,
            ]);
            $teacher->roles()->syncWithoutDetaching([$teacherRole->id]);
        }

        $course = Course::query()
            ->where('name', 'Evangelismo Eficaz')
            ->where('type', 'Clínica')
            ->orderBy('id')
            ->first();

        if (! $course) {
            $course = Course::query()->create([
                'order' => '0',
                'execution' => 0,
                'initials' => 'ev²',
                'price' => '180,00',
                'name' => 'Evangelismo Eficaz',
                'type' => 'Clínica',
                'targetAudience' => 'Membros da igreja local.',
            ]);
        }

        $training = Training::query()->updateOrCreate(
            ['notes' => self::TRAINING_MARKER],
            [
                'coordinator' => 'Secretaria da Clínica EE',
                'phone' => '21972765535',
                'email' => 'clinica.ee.seed@eebrasil.org.br',
                'street' => 'Rua da Clínica',
                'number' => '100',
                'district' => 'Centro',
                'city' => 'Rio de Janeiro',
                'state' => 'RJ',
                'postal_code' => '20000000',
                'price' => '180.00',
                'price_church' => '20.00',
                'discount' => '0.00',
                'status' => TrainingStatus::Scheduled->value,
                'welcome_duration_minutes' => 30,
                'course_id' => $course->id,
                'teacher_id' => $teacher->id,
                'church_id' => $church->id,
            ]
        );

        $this->seedEventDates($training);

        $mentors = $this->pickUsers(exceptIds: [$teacher->id], quantity: 5);
        $students = $this->pickUsers(
            exceptIds: array_merge([$teacher->id], $mentors->pluck('id')->all()),
            quantity: 10
        );

        $mentors->each(function (User $mentor) use ($mentorRole): void {
            $mentor->roles()->syncWithoutDetaching([$mentorRole->id]);
        });

        $students->each(function (User $student) use ($studentRole): void {
            $student->forceFill([
                'church_id' => null,
                'church_temp_id' => null,
            ])->save();
            $student->roles()->syncWithoutDetaching([$studentRole->id]);
        });

        $training->mentors()->sync(
            $mentors->mapWithKeys(fn (User $mentor): array => [
                $mentor->id => ['created_by' => $teacher->id],
            ])->all()
        );

        $training->students()->sync(
            $students->mapWithKeys(fn (User $student): array => [
                $student->id => [
                    'accredited' => 0,
                    'kit' => 0,
                    'payment' => 0,
                    'payment_receipt' => null,
                ],
            ])->all()
        );
    }

    private function seedEventDates(Training $training): void
    {
        $friday = CarbonImmutable::now()->next(CarbonImmutable::FRIDAY)->startOfDay();
        $dates = [
            ['date' => $friday->toDateString(), 'start_time' => '18:30:00', 'end_time' => '21:30:00'],
            ['date' => $friday->addDay()->toDateString(), 'start_time' => '08:00:00', 'end_time' => '21:30:00'],
            ['date' => $friday->addDays(2)->toDateString(), 'start_time' => '08:00:00', 'end_time' => '18:30:00'],
        ];

        EventDate::query()->where('training_id', $training->id)->delete();

        foreach ($dates as $slot) {
            EventDate::query()->create([
                'training_id' => $training->id,
                'date' => $slot['date'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
            ]);
        }
    }

    /**
     * @param  array<int, int>  $exceptIds
     * @return Collection<int, User>
     */
    private function pickUsers(array $exceptIds, int $quantity): Collection
    {
        $users = User::query()
            ->whereNotIn('id', $exceptIds)
            ->orderBy('id')
            ->limit($quantity)
            ->get();

        if ($users->count() >= $quantity) {
            return $users;
        }

        $missing = $quantity - $users->count();
        $createdUsers = User::factory()->count($missing)->create([
            'church_id' => null,
            'church_temp_id' => null,
        ]);

        return $users->concat($createdUsers)->values();
    }
}
