<?php

namespace App\Http\Repositories\User;

use App\Exceptions\Plan\PlanNotFoundException;
use App\Exceptions\User\UserAlreadyExistsException;
use App\Exceptions\User\UserEmailDoesNotMatchException;
use App\Exceptions\User\UserHasEncryptedDataException;
use App\Exceptions\User\UserInvalidPasswordException;
use App\Exceptions\User\UserNotAllowedException;
use App\Exceptions\User\UserNotFoundException;
use App\Exceptions\User\UserOldPasswordIsIncorrectException;
use App\Exceptions\User\UserPasswordDidNotChangeException;
use App\Exceptions\User\UserPasswordDoesNotMatchException;
use App\Mail\SendUserRegistrationMail;
use App\Models\Plan;
use App\Models\Sale;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

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

        if ($user->hasEncryptedData()) {
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

    public function removeUser(int $userId) {
        $user = $this->getLoggedInUser();

        if (!$user->isAdmin() || $user->id == $userId) {
            throw new UserNotAllowedException();
        }

        $userToDelete = User::find($userId);
        if (!$userToDelete) {
            throw new UserNotFoundException();
        }

        if ($userToDelete->hasEncryptedData()) {
            throw new UserHasEncryptedDataException();
        }

        $userToDelete->sales()->delete();
        $userToDelete->passwords()->delete();
        $userToDelete->folders()->delete();
        $userToDelete->delete();

        return true;
    }

    public function createUser(string $name, string $email, int $planId, bool $admin) : User {
        $user = $this->getLoggedInUser();

        if (!$user->isAdmin()) {
            throw new UserNotAllowedException();
        }

        $userAlreadyExist = User::where('email', $email)->exists();
        if ($userAlreadyExist) {
            throw new UserAlreadyExistsException();
        }

        $plan = Plan::find($planId);
        if ($plan == null) {
            throw new PlanNotFoundException();
        }

        // START USER
        $model = new User();
        $model->name = $name;
        $model->email = $email;
        $model->status_id = 1; // 1 = PENDENTE
        $model->remember_token = Str::random(10);
        $model->admin = $admin;
        $model->save();
        $newUser = $model->fresh();
        // END USER

        $sale = new Sale();
        $sale->user_id = $newUser->id;
        $sale->code = "manual_". $email;
        $sale->plan_id = $plan->id;
        $sale->value_total = 0;
        $sale->final_value = 0;
        $sale->status_id = 4;
        $sale->created_at = Carbon::now();
        $sale->updated_at = Carbon::now();
        $sale->transaction_body = "Manually created via Frontend";
        $sale->save();

        Mail::to($newUser->email)->send(new SendUserRegistrationMail($newUser));

        return $newUser;
    }
}
