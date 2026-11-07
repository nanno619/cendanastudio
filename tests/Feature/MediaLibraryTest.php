<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function test_files_attach_to_a_model_on_the_public_disk(): void
    {
        Storage::fake('public');
        $owner = MediaOwner::query()->findOrFail(User::factory()->create()->id);

        $media = $owner->addMedia(UploadedFile::fake()->image('logo.png', 120, 80))
            ->toMediaCollection('documents');

        $this->assertSame('logo.png', $owner->getFirstMedia('documents')->file_name);
        Storage::disk('public')->assertExists($media->getPathRelativeToRoot());
    }
}

/** Test-only model so the suite doesn't decide which app models own media. */
class MediaOwner extends User implements HasMedia
{
    use InteractsWithMedia;

    protected $table = 'users';
}
