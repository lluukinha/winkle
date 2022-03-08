<?php

namespace Tests\Feature\Folder;

use App\Models\User;
use App\Models\Folder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolderSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testListPasswords() {
        $user = User::factory()->create();
        $folder = Folder::factory()->create([ 'user_id' => $user->id ]);

        $response = $this->actingAs($user)->getJson("/api/folders");
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'id' => (string) $folder->id,
                        'name' => (string) $folder->name,
                    ]
                ]
            ]);
    }

    public function testCreateFolder() {
        $user = User::factory()->create();
        $data = [ "name" => "redes sociais" ];
        $response = $this->actingAs($user)->postJson("/api/folders", $data);
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    "name" => strtoupper($data["name"]),
                ]
            ]);
    }

    public function testUpdateFolder() {
        $user = User::factory()->create();
        $folder = Folder::factory()
            ->create(['user_id' => $user->id]);

        $data = [ "name" => "new folder name" ];

        $response = $this->actingAs($user)
            ->putJson("/api/folders/$folder->id", $data);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    "name" => "NEW FOLDER NAME"
                ]
            ]);
    }
}
