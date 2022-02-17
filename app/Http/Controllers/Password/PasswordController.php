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
use App\Exceptions\Password\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Http\Resources\Password\PasswordFolderResource;
use App\Models\Folder;
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
                if ($key == "folder") continue;
                $pass[$key] = $key === "password" || $key === "login"
                    ? Crypt::encryptString($value)
                    : $value;
            }

            if (array_key_exists("folder", $attributes)) {
                $folder = $attributes["folder"];
                if (is_null($folder["id"])) {
                    $f = new Folder();
                    $f->user_id = Auth::user()->id;
                    $f->model = "passwords";
                    $f->name = $folder["name"];
                    $f->save();
                } else {
                    $f = Folder::find($folder["id"]);
                    if (!$f) {
                        throw new FolderNotFoundException();
                    }
                }

                $pass->folder_id = $f->id;
            }

            $pass->save();
            return new PasswordResource($pass);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'already-exists');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
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
                if ($key == "folder") continue;
                $pass[$key] = $key === "password" || $key === "login"
                    ? Crypt::encryptString($value)
                    : $value;
            }

            if (array_key_exists("folder", $attributes)) {
                $willRemoveCurrentFolder = false;
                $currentFolder = $pass->folder;
                $folder = $attributes["folder"];
                if (is_null($folder["id"]) || $folder["id"] == "") {
                    $f = new Folder();
                    $f->user_id = Auth::user()->id;
                    $f->model = "passwords";
                    $f->name = $folder["name"];
                    $f->save();
                } else {
                    $f = Folder::find($folder["id"]);
                    if (!$f) {
                        throw new FolderNotFoundException();
                    }
                }

                if ($currentFolder && $currentFolder->passwords->count() < 2) {
                    $willRemoveCurrentFolder = true;
                }

                $pass->folder_id = $f->id;
                $pass->save();

                if ($willRemoveCurrentFolder) {
                    $folderToRemove = Folder::find($currentFolder->id);
                    $folderToRemove->delete();
                }
            }
            return new PasswordResource($pass);
        } catch (PasswordAlreadyExistsException $e) {
            throw Http422::makeForField('password', 'already-exists');
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        }
    }

    public function delete($id) {
        try {
            $pass = Auth::user()->passwords()->find($id);
            if (!$pass) {
                throw new PasswordNotFoundException();
            }

            $willDeleteFolder= false;
            $folder = $pass->folder;

            if ($folder && $folder->passwords->count() < 2) {
                $willDeleteFolder = true;
            }

            $pass->delete();
            $folder->delete();

            return true;
        } catch (PasswordNotFoundException $e) {
            throw Http404::makeForField('password', 'not-found');
        }
    }
}
