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

    public function testUpdateUserEmailUsingWrongPassword() {
        $user = User::factory()->create();
        $data = [
            "email" => "marcolino@teste.com",
            "confirmEmail" => "marcolino@teste.com",
            "password" => "Incorrect" // correct is password
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/email", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "password-incorrect",
                        "source" => [ "parameter" => "password" ]
                    ]
                ]
            ]);
    }

    public function testUpdateUserPasswordSendingWrongPassword() {
        $user = User::factory()->create();
        $data = [
            "password" => "oldPassword", // correct is password
            "newPassword" => "lucasPass",
            "confirmNewPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/password", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "password-incorrect",
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
            "confirmNewMasterPassword" => "lucasPass",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/masterPassword", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "password-incorrect",
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
            "confirmNewMasterPassword" => "lucasPass",
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

    public function testUpdateUserPasswordUsingSmallerNewPassword() {
        $user = User::factory()->create();
        $data = [
            "password" => "password",
            "newPassword" => "lucas", // should have at least 6 characters
            "confirmNewPassword" => "lucas",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/password", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "must-be-at-least-6",
                        "source" => [ "parameter" => "newPassword"]
                    ]
                ]
            ]);
    }

    public function testUpdateUserMasterPasswordUsingSmallerMasterPassword() {
        $user = User::factory()->create();
        $data = [
            "password" => "password",
            "oldMasterPassword" => "password",
            "newMasterPassword" => "lucas", // Should have at least 6 characters
            "confirmNewMasterPassword" => "lucas",
        ];

        $response = $this->actingAs($user)
            ->putJson("/api/user/masterPassword", $data);

        $response
            ->assertStatus(422)
            ->assertJson([
                'errors' => [
                    [
                        "title" => "must-be-at-least-6",
                        "source" => [ "parameter" => "newMasterPassword"]
                    ]
                ]
            ]);
    }
}
