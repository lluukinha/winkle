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

    public function testShowPassword() {
        $user = User::factory()->create();
        $pass = Password::factory()->create([ 'user_id' => $user->id ]);

        $response = $this->actingAs($user)->getJson("/api/passwords/$pass->id");
        $response
            ->assertStatus(200)
            ->assertJson([
                'data' => [
                    'type' => 'passwords',
                    'id' => (string) $pass->id,
                ]
            ]);
    }
}
