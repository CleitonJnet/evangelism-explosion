<?php

use App\Livewire\Pages\App\Director\Website\Testimonials\CreateModal;
use App\Livewire\Pages\App\Director\Website\Testimonials\Index;
use App\Models\Role;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

uses(RefreshDatabase::class);

function createDirector(): User
{
    $director = User::factory()->create();
    $role = Role::query()->firstOrCreate(['name' => 'Director']);

    $director->roles()->syncWithoutDetaching([$role->id]);

    return $director;
}

function assertTestimonialPhotoIsProcessed(string $path): void
{
    expect(pathinfo($path, PATHINFO_EXTENSION))->toBe('webp');

    $imageInfo = getimagesize(Storage::disk('public')->path($path));

    expect($imageInfo)->not->toBeFalse();
    expect($imageInfo[0])->toBe(1280);
    expect($imageInfo[1])->toBe(1040);
}

it('renders the director testimonials page', function () {
    $director = createDirector();

    $response = $this
        ->actingAs($director)
        ->get(route('app.director.testimonials'));

    $response->assertOk();
    $response->assertSeeText('Gerenciamento de Testemunhos');
});

it('creates a testimonial through the nested create modal component', function () {
    $director = createDirector();
    Storage::fake('public');
    $photo = UploadedFile::fake()->image('samuel.jpg');

    Livewire::actingAs($director)
        ->test(CreateModal::class)
        ->call('openModal')
        ->set('name', 'Pr. Samuel Dias')
        ->set('meta', 'Igreja Central - Sao Paulo/SP')
        ->set('quote', 'Esse treinamento mudou nossa cultura de evangelizacao.')
        ->set('photoUpload', $photo)
        ->set('isActive', true)
        ->call('save')
        ->assertSet('showModal', false);

    $this->assertDatabaseHas('testimonials', [
        'name' => 'Pr. Samuel Dias',
        'meta' => 'Igreja Central - Sao Paulo/SP',
        'position' => 1,
        'is_active' => 1,
    ]);

    $testimonial = Testimonial::query()->where('name', 'Pr. Samuel Dias')->firstOrFail();
    expect($testimonial->photo)->not->toBeNull();
    Storage::disk('public')->assertExists((string) $testimonial->photo);
    assertTestimonialPhotoIsProcessed((string) $testimonial->photo);
});

it('edits and removes a testimonial from the director listing component', function () {
    $director = createDirector();
    Storage::fake('public');

    Storage::disk('public')->put('testimonials/photos/old-photo.jpg', 'old-image');

    $testimonial = Testimonial::factory()->create([
        'name' => 'Maria Antiga',
        'meta' => 'Igreja Antiga',
        'quote' => 'Texto original',
        'photo' => 'testimonials/photos/old-photo.jpg',
        'position' => 2,
        'is_active' => true,
    ]);

    $newPhoto = UploadedFile::fake()->image('maria.jpg');

    $component = Livewire::actingAs($director)
        ->test(Index::class)
        ->call('openEditModal', $testimonial->id)
        ->set('editName', 'Maria Atualizada')
        ->set('editMeta', 'Igreja Renovada')
        ->set('editQuote', 'Texto atualizado para publicacao.')
        ->set('editPhotoUpload', $newPhoto)
        ->call('saveEditedTestimonial');

    $testimonial->refresh();
    expect($testimonial->photo)->not->toBe('testimonials/photos/old-photo.jpg');
    expect($testimonial->photo)->not->toBeNull();
    Storage::disk('public')->assertMissing('testimonials/photos/old-photo.jpg');
    Storage::disk('public')->assertExists((string) $testimonial->photo);
    assertTestimonialPhotoIsProcessed((string) $testimonial->photo);
    $component->assertSee((string) $testimonial->photo);

    Livewire::actingAs($director)
        ->test(Index::class)
        ->call('openDeleteModal', $testimonial->id)
        ->call('deleteSelectedTestimonial');

    $this->assertDatabaseMissing('testimonials', [
        'id' => $testimonial->id,
    ]);
    Storage::disk('public')->assertMissing((string) $testimonial->photo);
});

it('shows only active testimonials on the public home component', function () {
    Testimonial::factory()->create([
        'name' => 'Publico Ativo',
        'quote' => 'Este deve aparecer no site.',
        'is_active' => true,
        'position' => 1,
    ]);

    Testimonial::factory()->create([
        'name' => 'Publico Inativo',
        'quote' => 'Este nao pode aparecer no site.',
        'is_active' => false,
        'position' => 0,
    ]);

    Livewire::test(\App\Livewire\Web\Home\Testimonials::class)
        ->assertSee('Publico Ativo')
        ->assertDontSee('Publico Inativo');
});

it('does not render testimonials section when there is no active testimonial', function () {
    Testimonial::factory()->create([
        'name' => 'Inativo',
        'quote' => 'Nao deve aparecer.',
        'is_active' => false,
    ]);

    Livewire::test(\App\Livewire\Web\Home\Testimonials::class)
        ->assertDontSee('Testemunhos')
        ->assertDontSee('Inativo');
});

it('reorders testimonials when moving a row in the director list', function () {
    $director = createDirector();

    $first = Testimonial::factory()->create([
        'name' => 'Primeiro',
        'position' => 1,
    ]);

    $second = Testimonial::factory()->create([
        'name' => 'Segundo',
        'position' => 2,
    ]);

    $third = Testimonial::factory()->create([
        'name' => 'Terceiro',
        'position' => 3,
    ]);

    Livewire::actingAs($director)
        ->test(Index::class)
        ->call('moveAfter', $first->id, null);

    expect(Testimonial::query()->findOrFail($first->id)->position)->toBe(3);
    expect(Testimonial::query()->findOrFail($third->id)->position)->toBe(2);
    expect(Testimonial::query()->findOrFail($second->id)->position)->toBe(1);
});

it('toggles testimonial status directly from listing', function () {
    $director = createDirector();

    $testimonial = Testimonial::factory()->create([
        'is_active' => true,
    ]);

    Livewire::actingAs($director)
        ->test(Index::class)
        ->call('toggleStatus', $testimonial->id, false);

    expect(Testimonial::query()->findOrFail($testimonial->id)->is_active)->toBeFalse();
});
