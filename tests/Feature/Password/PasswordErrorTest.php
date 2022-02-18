<?php

namespace Tests\Feature\Password;

use App\Models\Password;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordErrorTest extends TestCase
{
    use RefreshDatabase;

    public function testCreatePasswordWithoutName() {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();

        $data = [
            "url" => "https://www.teste.com.br"
        ];

        $response = $this->actingAs($user)->postJson("/api/passwords", $data);
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

    public function testUpdatePasswordFromAnotherUser() {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $password = Password::factory()
            ->create(['user_id' => $anotherUser->id]);

        $data = [
            "name" => $password->name,
            "url" => "https://www.teste.com.br"
        ];

        $response = $this->actingAs($user)->putJson("/api/passwords/$password->id", $data);
        $response
            ->assertStatus(404)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'not-found',
                        'source' => [
                            'parameter' => 'password'
                        ]
                    ]
                ]
            ]);
    }

    public function testUpdatePasswordUsingExistingName() {
        $user = User::factory()->create();
        $existingPassword = Password::factory()->create(['user_id' => $user->id]);
        $password = Password::factory()->create(['user_id' => $user->id]);

        $data = [
            "name" => $existingPassword->name,
            "url" => "https://www.teste.com.br"
        ];

        $response = $this->actingAs($user)->putJson("/api/passwords/$password->id", $data);
        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        'title' => 'already-exists',
                        'source' => [
                            'parameter' => 'password'
                        ]
                    ]
                ]
            ]);
    }
}
