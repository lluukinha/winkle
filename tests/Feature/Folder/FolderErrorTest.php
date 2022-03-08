<?php

namespace Tests\Feature\Folder;

use App\Models\Password;
use App\Models\Folder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderErrorTest extends TestCase
{
    use RefreshDatabase;

    public function testCreateFolderWithoutName() {
        $user = User::factory()->create();

        $data = [];

        $response = $this->actingAs($user)->postJson("/api/folders", $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'name-required',
                        'source' => [
                            'parameter' => 'name'
                        ]
                    ]
                ]
            ]);
    }

    public function testUpdateFolderFromAnotherUser() {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $folder = Folder::factory()
            ->create(['user_id' => $anotherUser->id]);

        $data = [ "name" => "new name" ];

        $response = $this->actingAs($user)->putJson("/api/folders/$folder->id", $data);
        $response
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'not-found',
                        'source' => [
                            'parameter' => 'folder'
                        ]
                    ]
                ]
            ]);
    }

    public function testUpdateFolderUsingExistingName() {
        $user = User::factory()->create();
        $existingFolder = Folder::factory()->create(['user_id' => $user->id]);
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        $data = [ "name" => $existingFolder->name ];

        $response = $this->actingAs($user)->putJson("/api/folders/$folder->id", $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'already-exists',
                        'source' => [
                            'parameter' => 'name'
                        ]
                    ]
                ]
            ]);
    }

    public function testDeleteFolderWithPasswords() {
        $user = User::factory()->create();
        $folder = Folder::factory()->create(['user_id' => $user->id]);

        Password::factory()->create([ 'user_id' => $user->id, 'folder_id' => $folder->id ]);

        $response = $this->actingAs($user)->deleteJson("/api/folders/$folder->id");
        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'folder-has-passwords',
                        'source' => [
                            'parameter' => 'folder'
                        ]
                    ]
                ]
            ]);
    }
}
