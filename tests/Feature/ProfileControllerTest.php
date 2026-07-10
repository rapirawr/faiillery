<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_update_accepts_avatar_upload_without_email_field(): void
    {
        Storage::fake('s3');

        $user = User::factory()->create([
            'name' => 'Old Name',
            'username' => 'oldname',
            'email' => 'old@example.com',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => 'New Name',
                'username' => 'newname',
                'bio' => 'Hello world',
                'avatar' => UploadedFile::fake()->image('avatar.jpg', 400, 400),
            ]);

        $response->assertRedirect(route('profile.edit'));
        $response->assertSessionHas('status', 'profile-updated');

        $user->refresh();
        $this->assertNotNull($user->avatar);
        Storage::disk('s3')->assertExists($user->avatar);
    }
}
