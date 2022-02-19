<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Http\Resources\User\UserResource;
use App\Http\Requests\User\UpdateUserEmailRequest;
use App\Http\Requests\User\UpdateUserMasterPasswordRequest;
use App\Http\Requests\User\UpdateUserPasswordRequest;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\User\UserEmailDoesNotMatchException;
use App\Exceptions\User\UserHasEncryptedDataException;
use App\Exceptions\User\UserInvalidPasswordException;
use App\Exceptions\User\UserNotFoundException;
use App\Exceptions\User\UserOldPasswordIsIncorrectException;
use App\Exceptions\User\UserPasswordDidNotChangeException;
use App\Exceptions\User\UserPasswordDoesNotMatchException;
use App\Http\Repositories\User\UserRepository;

class UserController extends Controller
{
    public function show() {
        $user = Auth::user();
        return new UserResource($user);
    }

    public function updateEmail(UpdateUserEmailRequest $request) {
        try {
            $attributes = $request->validated();
            $repository = new UserRepository();
            $user = $repository->updateEmail(
                $attributes["email"],
                $attributes["confirmEmail"],
                $attributes["password"]
            );
            return new UserResource($user);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'not-found');
        } catch (UserEmailDoesNotMatchException $e) {
            throw Http422::makeForField('email', 'emails-does-not-match');
        } catch (UserInvalidPasswordException $e) {
            throw Http422::makeForField('password', 'password-incorrect');
        }
    }

    public function updatePassword(UpdateUserPasswordRequest $request) {
        try {
            $attributes = $request->validated();
            $repository = new UserRepository();
            $user = $repository->updatePassword(
                $attributes["password"],
                $attributes["newPassword"],
                $attributes["confirmNewPassword"],
            );
            return new UserResource($user);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'not-found');
        } catch (UserOldPasswordIsIncorrectException $e) {
            throw Http422::makeForField('oldPassword', 'password-incorrect');
        } catch (UserPasswordDidNotChangeException $e) {
            throw Http422::makeForField('password', 'password-is-equal');
        } catch (UserPasswordDoesNotMatchException $e) {
            throw Http422::makeForField('password', 'password-does-not-match');
        }
    }

    public function updateMasterPassword(UpdateUserMasterPasswordRequest $request) {
        try {
            $attributes = $request->validated();
            $repository = new UserRepository();
            $user = $repository->updateMasterPassword(
                $attributes["password"],
                $attributes["oldMasterPassword"],
                $attributes["newMasterPassword"]
            );
            return new UserResource($user);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'not-found');
        } catch (UserOldPasswordIsIncorrectException $e) {
            throw Http422::makeForField('oldPassword', 'master-password-incorrect');
        } catch (UserPasswordDidNotChangeException $e) {
            throw Http422::makeForField('master-password', 'password-is-equal');
        } catch (UserInvalidPasswordException $e) {
            throw Http422::makeForField('password', 'password-incorrect');
        } catch (UserHasEncryptedDataException $e) {
            throw Http422::makeForField('master-password', 'has-encrypted-data');
        }
    }
}
