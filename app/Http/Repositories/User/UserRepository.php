<?php

namespace App\Http\Repositories\User;

use App\Exceptions\Password\FolderHasPasswordsException;
use App\Exceptions\Password\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Exceptions\User\UserEmailDoesNotMatchException;
use App\Exceptions\User\UserHasEncryptedDataException;
use App\Exceptions\User\UserInvalidPasswordException;
use App\Exceptions\User\UserNotFoundException;
use App\Exceptions\User\UserOldPasswordIsIncorrectException;
use App\Exceptions\User\UserPasswordDidNotChangeException;
use App\Http\Models\Password\PasswordModel;
use App\Models\Folder;
use App\Models\Password;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserRepository {

    private function getLoggedInUser() : User {
        $userId = Auth::user()->id;
        $user = User::find($userId);
        if (!$user) throw new UserNotFoundException();
        return $user;
    }

    public function updateEmail(string $email, string $confirmEmail, string $password) : User {
        $user = $this->getLoggedInUser();

        if (!Hash::check($password, $user->password)) {
            throw new UserInvalidPasswordException();
        }

        if ($email != $confirmEmail) {
            throw new UserEmailDoesNotMatchException();
        }

        $user->email = $email;
        $user->save();
        return $user;
    }

    public function updatePassword(string $oldPassword, string $newPassword) : User {
        $user = $this->getLoggedInUser();
        $currentPassword = $user->password;

        if (!Hash::check($oldPassword, $currentPassword)) {
            throw new UserOldPasswordIsIncorrectException();
        }

        if (Hash::check($newPassword, $currentPassword)) {
            throw new UserPasswordDidNotChangeException();
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return $user;
    }

    public function updateMasterPassword(
        string $password,
        string $oldMasterPassword,
        string $newMasterPassword
    ) : User {

        $user = $this->getLoggedInUser();

        if (!Hash::check($password, $user->password)) {
            throw new UserInvalidPasswordException();
        }

        if (!$user->canUpdateMasterPassword()) {
            throw new UserHasEncryptedDataException();
        }

        if (!Hash::check($oldMasterPassword, $user->master_password)) {
            throw new UserOldPasswordIsIncorrectException();
        }

        if (Hash::check($newMasterPassword, $user->master_password)) {
            throw new UserPasswordDidNotChangeException();
        }

        $user->master_password = Hash::make($newMasterPassword);
        $user->save();
        return $user;
    }
}
