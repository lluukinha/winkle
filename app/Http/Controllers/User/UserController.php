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
use App\Exceptions\User\UserHasInvalidTokenException;
use App\Exceptions\User\UserHasResetPasswordInProgressException;
use App\Exceptions\User\UserInvalidPasswordException;
use App\Exceptions\User\UserNotAllowedException;
use App\Exceptions\User\UserNotFoundException;
use App\Exceptions\User\UserOldPasswordIsIncorrectException;
use App\Exceptions\User\UserPasswordDidNotChangeException;
use App\Exceptions\User\UserPasswordDoesNotMatchException;
use App\Http\Repositories\User\UserRepository;
use App\Http\Requests\User\FinishRegistrationRequest;
use App\Http\Requests\User\RedefineUserPasswordRequest;
use App\Http\Requests\User\VerifyRegistrationRequest;
use App\Mail\SendForgotPasswordMail;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function verifyRegistration(VerifyRegistrationRequest $request) {
        try {
            $attributes = $request->validated();

            $fields = [
                'email' => $attributes['email'],
                'remember_token' => $attributes['token'],
                'status_id' => 1
            ];

            $user = User::where($fields)
                ->whereNull('password')
                ->whereNull('master_password')
                ->first();

            if (!$user || (is_null($user->expirationDate()) || $user->expirationDate() < Carbon::now())) {
                throw new UserNotFoundException();
            }

            return response()->json(true);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'not-found');
        }
    }

    public function finishRegistration(FinishRegistrationRequest $request) {
        try {
            $attributes = $request->validated();

            $fields = [
                'email' => $attributes['email'],
                'remember_token' => $attributes['token'],
                'status_id' => 1
            ];

            $user = User::where($fields)
                ->whereNull('password')
                ->whereNull('master_password')
                ->first();

            if (!$user) {
                throw new UserNotFoundException();
            }

            $passwordsDoesNotMatch = ($attributes["password"] != $attributes["confirmPassword"])
                || ($attributes["masterPassword"] != $attributes["confirmMasterPassword"]);

            if ($passwordsDoesNotMatch) {
                throw new UserPasswordDoesNotMatchException();
            }

            $user->name = $attributes['name'];
            $user->area_code = $attributes['area_code'];
            $user->phone = $attributes['phone'];
            $user->password = Hash::make($attributes['password']);
            $user->master_password = Hash::make($attributes['masterPassword']);
            $user->status_id = 2;
            $user->email_verified_at = Carbon::now();
            $user->save();

            return new UserResource($user);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'not-found');
        } catch (UserPasswordDoesNotMatchException $e) {
            throw Http422::makeForField('password', 'password-does-not-match');
        }
    }

    public function resetPassword(RedefineUserPasswordRequest $request) {
        try {
            $attributes = $request->validated();

            if ($attributes["password"] != $attributes["confirmPassword"]) {
                throw new UserPasswordDoesNotMatchException();
            }

            $user = User::where('email', $attributes['email'])
                ->where(function ($query) use ($attributes) {
                    $query->where('remember_token', '!=', $attributes['token'])
                        ->orWhereNull('remember_token');
                })
                ->first();

            if (!$user) {
                throw new UserNotFoundException();
            }

            if (Hash::check($attributes["password"], $user->password)) {
                throw new UserPasswordDidNotChangeException();
            }

            $hasReset = DB::table('password_resets')
                ->where([
                    'email' => $user->email,
                    'token' => $attributes['token']
                ])
                ->where('expires_at', '>=', Carbon::now())
                ->exists();

            if (!$hasReset) {
                throw new UserHasInvalidTokenException();
            }

            $user->password = Hash::make($attributes["password"]);
            $user->remember_token = $attributes['token'];
            $user->save();

            return response()->json(true);
        } catch (UserHasInvalidTokenException $e) {
            throw Http422::makeForField('token', 'invalid-token');
        } catch (UserPasswordDidNotChangeException $e) {
            throw Http422::makeForField('password', 'password-is-equal');
        } catch (UserPasswordDoesNotMatchException $e) {
            throw Http422::makeForField('password', 'password-does-not-match');
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('user', 'token-or-user-not-found');
        }
    }

    public function forgotPassword(Request $request)
    {
        try {
            $email = $request->email;
            $user = User::whereEmail($email)->first();
            if (!$user) throw new UserNotFoundException();

            $hasReset = DB::table('password_resets')
                ->where([ 'email' => $user->email ])
                ->where('expires_at', '>', Carbon::now())
                ->exists();

            if ($hasReset) {
                throw new UserHasResetPasswordInProgressException();
            }

            $token = Str::random(10);
            DB::table('password_resets')->insert([
              'email' => $request->email,
              'token' => $token,
              'created_at' => Carbon::now(),
              'expires_at' => Carbon::now()->addDay(1)
            ]);

            Mail::to($email)->send(new SendForgotPasswordMail($user, $token));
            return response()->json(true);
        } catch (UserNotFoundException $e) {
            throw Http404::makeForField('email', 'forgot-password-user-not-found');
        } catch (UserHasResetPasswordInProgressException $e) {
            throw Http422::makeForField('user', 'user-has-reset-password-in-progress');
        }
    }

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
                $attributes["newMasterPassword"],
                $attributes["confirmNewMasterPassword"]
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

    public function list() {
        try {
            $repository = new UserRepository();
            $users = $repository->listAllUsers();
            return UserResource::collection($users);
        } catch (UserNotAllowedException $e) {
            throw Http422::makeForField('user', 'user-not-allowed');
        }
    }
}
