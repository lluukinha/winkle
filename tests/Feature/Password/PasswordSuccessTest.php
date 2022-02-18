<?php

namespace Tests\Feature\Password;

use App\Models\Password;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordSuccessTest extends TestCase
{
    use RefreshDatabase;

    public function testListPasswords() {
        $user = User::factory()->create();
        $pass = Password::factory()->create([ 'user_id' => $user->id ]);

        $response = $this->actingAs($user)->getJson("/api/passwords");
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    [
                        'type' => 'passwords',
                        'id' => (string) $pass->id,
                    ]
                ]
            ]);
    }

    public function testCreatePassword() {
        $user = User::factory()->create();

        $data = [
            "name" => "Quindim do Marcos",
            "url" => "https://www.teste.com.br"
        ];

        $response = $this->actingAs($user)->postJson("/api/passwords", $data);
        $response
            ->assertStatus(201)
            ->assertJson([
                'data' => [
                    "name" => $data["name"],
                    "url" => $data["url"]
                ]
            ]);
    }

    public function testUpdatePassword() {
        $user = User::factory()->create();
        $password = Password::factory()
            ->create(['user_id' => $user->id]);

        $data = [
            "name" => $password->name,
            "url" => "https://www.teste.com.br"
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/passwords/$password->id", $data);
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    "url" => $data["url"]
                ]
            ]);
    }
}
