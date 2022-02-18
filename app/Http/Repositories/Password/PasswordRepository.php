<?php

namespace App\Http\Repositories\Password;

use App\Exceptions\Password\FolderHasPasswordsException;
use App\Exceptions\Password\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;
use App\Http\Models\Password\PasswordModel;
use App\Models\Folder;
use App\Models\Password;
use Illuminate\Support\Facades\Auth;

class PasswordRepository {

    private function retrievePasswordFromId(int $id) : Password {
        $password = Auth::user()->passwords->find($id);
        if (!$password) throw new PasswordNotFoundException();
        return $password;
    }

    public function retrieveList() {
        return Auth::user()->passwords;
    }

    private function checkIfExists(string $nameToCheck, int $currentId = null) : bool {
        $list = Auth::user()->passwords();
        if (!is_null($currentId)) $list = $list->where('id', '<>', $currentId);
        return $list ->where('name', $nameToCheck)->exists();
    }

    private function createFolderFromName(string $folderName) : Folder {
        $folder = new Folder();
        $folder->user_id = Auth::user()->id;
        $folder->model = "passwords";
        $folder->name = strtoupper($folderName);
        $folder->save();
        return $folder;
    }

    private function retrieveFolderFromName(string $folderName) : Folder | null {
        return Auth::user()->folders->where('name', $folderName)->first();
    }

    private function retrieveFolderFromId(int $folderId) : Folder | null {
        return Auth::user()->folders->find($folderId);
    }

    private function removeFolderIfNeeded(int $folderId) : void {
        $folder = Auth::user()->folders->find($folderId);
        if ($folder->passwords->count() === 0) {
            $folder->delete();
        }
    }

    public function delete(int $passwordId) : void {
        $model = $this->retrievePasswordFromId($passwordId);
        if (!$model) throw new PasswordNotFoundException();
        $folder = $model->folder;
        $model->delete();
        if (!is_null($folder)) $this->removeFolderIfNeeded($folder->id);
    }

    public function create(PasswordModel $password) : Password {
        if ($this->checkIfExists($password->name)) throw new PasswordAlreadyExistsException();

        $model = new Password();
        $model->user_id = Auth::user()->id;
        $model->name = $password->name;
        if ($password->url) $model->url = $password->url;
        if ($password->login) $model->login = $password->login;
        if ($password->password) $model->password = $password->password;
        if ($password->description) $model->description = $password->description;

        if (!is_null($password->folder_name)) {
            if (is_null($password->folder_id)) {
                $folder = new Folder();
                $folder->user_id = Auth::user()->id;
                $folder->model = "passwords";
                $folder->name = $password->folder_name;
                $folder->save();
            }

            if (!is_null($password->folder_id)) {
                $folder = Folder::find($password->folder_id);
                if (!$folder) throw new FolderNotFoundException();
            }

            $model->folder_id = $folder->id;
        }

        $model->save();
        return $model;
    }

    public function update(PasswordModel $password) : Password {
        $model = $this->retrievePasswordFromId($password->id);

        if (is_null($password) || is_null($password->id)) throw new PasswordNotFoundException();

        if ($password->name) {
            if ($this->checkIfExists($password->name, $model->id)) throw new PasswordAlreadyExistsException();
            $model->name = $password->name;
        }

        if ($password->url) $model->url = $password->url;
        if ($password->login) $model->login = $password->login;
        if ($password->password) $model->password = $password->password;
        if ($password->description) $model->description = $password->description;

        $currentFolder = $model->folder;
        if (!is_null($password->folder_id) || !is_null($password->folder_name)) {
            if (is_null($password->folder_id)) {
                $existingFolder = $this->retrieveFolderFromName($password->folder_name);
                $folder = $existingFolder ?? $this->createFolderFromName($password->folder_name);
            }

            if (!is_null($password->folder_id)) {
                $folder = $this->retrieveFolderFromId($password->folder_id);
                if (!$folder) {
                    throw new FolderNotFoundException();
                }
            }

            $model->folder_id = $folder->id;
        }

        $clearFolderId = is_null($password->folder_id)
            && is_null($password->folder_name)
            && !is_null($currentFolder);
        if ($clearFolderId) $model->folder_id = null;

        $model->save();
        $model = $model->fresh();

        if (!is_null($currentFolder)) $this->removeFolderIfNeeded($currentFolder->id);

        return $model;
    }
}
