<?php

namespace App\Http\Repositories\User;

use App\Exceptions\User\UserEmailDoesNotMatchException;
use App\Exceptions\User\UserHasEncryptedDataException;
use App\Exceptions\User\UserInvalidPasswordException;
use App\Exceptions\User\UserNotAllowedException;
use App\Exceptions\User\UserNotFoundException;
use App\Exceptions\User\UserOldPasswordIsIncorrectException;
use App\Exceptions\User\UserPasswordDidNotChangeException;
use App\Exceptions\User\UserPasswordDoesNotMatchException;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
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

    public function updatePassword(string $password, string $newPassword, string $confirmNewPassword) : User {
        $user = $this->getLoggedInUser();

        if (!Hash::check($password, $user->password)) {
            throw new UserOldPasswordIsIncorrectException();
        }

        if ($newPassword !== $confirmNewPassword) {
            throw new UserPasswordDoesNotMatchException();
        }

        if (Hash::check($newPassword, $user->password)) {
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

    public function listAllUsers() : Collection {

        $user = $this->getLoggedInUser();

        if (!$user->isAdmin()) {
            throw new UserNotAllowedException();
        }

        $users = User::all();
        return $users;
    }
}
