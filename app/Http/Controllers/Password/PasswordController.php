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
use App\Exceptions\Password\FolderHasPasswordsException;
use App\Exceptions\Password\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Http\Models\Password\PasswordModel;
use App\Http\Repositories\Password\PasswordRepository;
use App\Http\Resources\Password\PasswordFolderResource;
use App\Models\Folder;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Crypt;

class PasswordController extends Controller
{
    public function list() {
        $passwords = Auth::user()->passwords;
        return PasswordResource::collection($passwords);
    }

    public function listFolders() {
        $folders = Auth::user()->folders()->where('model', 'passwords')->get();
        return PasswordFolderResource::collection($folders);
    }

    private function retrieveModel($attributes) : PasswordModel {
        return new PasswordModel(
            $attributes["name"],
            $attributes["url"] ?? null,
            $attributes["login"] ?? null,
            $attributes["password"] ?? null,
            $attributes["description"] ?? null,
            $attributes["folder"]["id"] ?? null,
            $attributes["folder"]["name"] ?? null,
        );
    }

    public function create(CreatePasswordRequest $request) {
        try {
            $attributes = $request->validated();
            $passwordModel = $this->retrieveModel($attributes);
            $repository = new PasswordRepository();
            $password = $repository->create($passwordModel);
            return new PasswordResource($password);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'already-exists');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        }
    }

    public function update(UpdatePasswordRequest $request, $id) {
        try {
            $attributes = $request->validated();
            $passwordModel = $this->retrieveModel($attributes);
            $passwordModel->setId($id);
            $repository = new PasswordRepository();
            $password = $repository->update($passwordModel);
            return new PasswordResource($password);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('password', 'already-exists');
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        } catch (FolderHasPasswordsException $e) {
            throw Http422::makeForField('folder', 'has-passwords');
        }
    }

    public function delete($id) {
        try {
            $repository = new PasswordRepository();
            $repository->delete($id);
            return true;
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }
}
