<?php

namespace Tests\Feature\Password;

use App\Models\Password;
use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordFolderSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testListPasswords() {
        $user = User::factory()->create();
        $folder = Folder::factory()->create([ 'user_id' => $user->id, 'model' => 'passwords' ]);
        // $pass = Password::factory()->create([ 'user_id' => $user->id, 'folder_id' => $folder->id ]);

        $response = $this->actingAs($user)->getJson("/api/passwords/folders");
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'type' => 'password_folders',
                        'id' => (string) $folder->id,
                        'name' => (string) $folder->name,
                    ]
                ]
            ]);
    }
}
