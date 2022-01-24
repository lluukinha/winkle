<?php

namespace App\Http\Controllers\Password;

use App\Exceptions\ApiExceptions\Http404;
use App\Models\Password;

use App\Http\Controllers\Controller;

use Illuminate\Http\Request;
use App\Http\Requests\Password\CreatePasswordRequest;

use App\Http\Resources\Password\PasswordResource;

use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Http\Requests\Password\UpdatePasswordRequest;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    public function list() {
        return PasswordResource::collection(Auth::user()->passwords);
    }

    public function show($id) {
        $pass = Auth::user()->passwords()->find($id);
        return new PasswordResource($pass);
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
                $pass[$key] = $value;
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
                $pass[$key] = $value;
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
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }
}
