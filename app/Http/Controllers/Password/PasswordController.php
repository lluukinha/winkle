<?php

namespace App\Http\Controllers\Password;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Http\Requests\Password\CreatePasswordRequest;
use App\Http\Requests\Password\UpdatePasswordRequest;

use App\Http\Resources\Password\PasswordResource;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\Folder\FolderHasPasswordsException;
use App\Exceptions\Folder\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Http\Models\Password\PasswordModel;
use App\Http\Repositories\Password\PasswordRepository;
use App\Http\Requests\Password\CreateManyPasswordRequest;
use Illuminate\Auth\Events\PasswordReset;

class PasswordController extends Controller
{
    public function list() {
        $passwords = Auth::user()->passwords;
        return PasswordResource::collection($passwords);
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

    private function retrieveModels($list) : array {
        $models = array_map(function($attributes) {
            return new PasswordModel(
                $attributes["name"],
                $attributes["url"] ?? null,
                $attributes["login"] ?? null,
                $attributes["password"] ?? null,
                $attributes["description"] ?? null,
                null,
                $attributes["folderName"] ?? null
            );
        }, $list);
        return $models;
    }

    public function createMany(CreateManyPasswordRequest $request) {
        try {
            $attributes = $request->validated();
            $passwordModels = $this->retrieveModels($attributes["list"]);
            $repository = new PasswordRepository();
            $response = $repository->createMany($passwordModels);

            $created = PasswordResource::collection($response['created']);
            $updated = PasswordResource::collection($response['updated']);

            return response()->json([ 'created' => $created, 'updated' => $updated ]);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'already-exists');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        }
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

    public function updateFolder(int $id, int $folderId) {
        try {
            $password = Auth::user()->passwords()->find($id);
            if (!$password) {
                throw new PasswordNotFoundException();
            }

            $folder = Auth::user()->folders()->find($folderId);
            if (!$folder) {
                throw new FolderNotFoundException();
            }

            $password->folder_id = $folderId;
            $password->save();

            return new PasswordResource($password->fresh());
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
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
            $isDeleted = $repository->delete($id);
            return response()->json($isDeleted);
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }
}
