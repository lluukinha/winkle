<?php

namespace Tests\Feature\Password;

use App\Models\Password;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PasswordErrorTest extends TestCase
{
    use RefreshDatabase;

    public function testShowPasswordFromAnotherUser() {
        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $pass = Password::factory()->create([ 'user_id' => $anotherUser->id ]);

        $response = $this->actingAs($user)->getJson("/api/passwords/$pass->id");
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
}
