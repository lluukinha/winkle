<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testShowUser() {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->getJson("/api/user");
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'users',
                    'name' => $user->name,
                    'email' => $user->email,
                    'canUpdateMasterPassword' => true
                ]
            ]);
    }

    public function testUpdateUserEmail() {
        $user = User::factory()->create();
        $data = [ "email" => "marcolino@gmail.com" ];
        $response = $this->actingAs($user)
            ->putJson("/api/user/email", $data);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'users',
                    'name' => $user->name,
                    'email' => $data["email"],
                    'canUpdateMasterPassword' => true
                ]
            ]);
    }

    public function testUpdateUserPassword() {
        $user = User::factory()->create();
        $data = [
            "oldPassword" => "password",
            "newPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/password", $data);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'users',
                    'id' => $user->id
                ]
            ]);
    }

    public function testUpdateUserMasterPassword() {
        $user = User::factory()->create();
        $data = [
            "password" => "password",
            "oldMasterPassword" => "password",
            "newMasterPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/masterPassword", $data);

        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'users',
                    'id' => $user->id
                ]
            ]);
    }
}
