<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Models\Password;

use App\Http\Requests\Password\CreatePasswordRequest;
use App\Http\Requests\Password\UpdatePasswordRequest;

use App\Http\Resources\Password\PasswordResource;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use Illuminate\Support\Facades\Crypt;

class PasswordController extends Controller
{
    public function list() {
        $passwords = Auth::user()->passwords;
        return PasswordResource::collection($passwords);
    }

    public function show($id) {
        try {
            $pass = Auth::user()->passwords()->find($id);
            if (!$pass) {
                throw new PasswordNotFoundException();
            }
            return new PasswordResource($pass);
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }

    public function create(CreatePasswordRequest $request) {
        try {
            $attributes = $request->validated();
            $passwordAlreadyExists = Auth::user()->passwords()->where('name', $attributes['name'])->exists();
            if ($passwordAlreadyExists) {
                throw new PasswordAlreadyExistsException();
            }

            $pass = new Password();
            $pass->user_id = Auth::user()->id;
            foreach ($attributes as $key => $value) {
                if ($key === "password") {
                    $pass[$key] = Crypt::encryptString($value);
                } else {
                    $pass[$key] = $value;
                }
            }
            $pass->save();
            return new PasswordResource($pass);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'already-exists');
        }
    }

    public function update(UpdatePasswordRequest $request, $id) {
        try {
            $pass = Auth::user()->passwords()->find($id);
            if (!$pass) {
                throw new PasswordNotFoundException();
            }

            $attributes = $request->validated();
            if (array_key_exists('name', $attributes)) {
                $passwordAlreadyExists = Auth::user()->passwords()
                    ->where('id', '<>', $id)
                    ->where('name', $attributes['name'])
                    ->exists();

                if ($passwordAlreadyExists) {
                    throw new PasswordAlreadyExistsException();
                }
            }

            foreach ($attributes as $key => $value) {
                if ($key === "password") {
                    $pass[$key] = Crypt::encryptString($value);
                } else {
                    $pass[$key] = $value;
                }
            }
            $pass->save();
            return new PasswordResource($pass);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('password', 'already-exists');
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }

    public function delete($id) {
        try {
            $pass = Auth::user()->passwords()->find($id);
            if (!$pass) {
                throw new PasswordNotFoundException();
            }
            $pass->delete();
            return true;
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }
}
