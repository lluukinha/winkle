<?php

namespace Tests\Feature\User;

use App\Models\Password;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserErrorTest extends TestCase
{
    use RefreshDatabase;

    public function testUpdateUserEmailUsingString() {
        $user = User::factory()->create();
        $data = [ "email" => "marcolino" ];
        $response = $this->actingAs($user)
            ->putJson("/api/user/email", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "must-be-email",
                        "source" => [ "parameter" => "email"]
                    ]
                ]
            ]);
    }

    public function testUpdateUserPasswordSendingWrongPassword() {
        $user = User::factory()->create();
        $data = [
            "oldPassword" => "oldPassword", // correct is password
            "newPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/password", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "incorrect",
                        "source" => [ "parameter" => "oldPassword"]
                    ]
                ]
            ]);
    }

    public function testUpdateUserMasterPasswordUsingWrongPassword() {
        $user = User::factory()->create();
        $data = [
            "password" => "wrongPassword", // correct is password
            "oldMasterPassword" => "password",
            "newMasterPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/masterPassword", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "incorrect",
                        "source" => [ "parameter" => "password"]
                    ]
                ]
            ]);
    }

    public function testUpdateUserMasterPasswordWhichHasEncryptedData() {
        $user = User::factory()->create();
        Password::factory()->create([
            'login' => 'websiteLogin',
            'password' => 'passwordForThisSite',
            'user_id' => $user->id
        ]);

        $data = [
            "password" => "password",
            "oldMasterPassword" => "password",
            "newMasterPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/masterPassword", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "has-encrypted-data",
                        "source" => [ "parameter" => "master-password"]
                    ]
                ]
            ]);
    }
}
