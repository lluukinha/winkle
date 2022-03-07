<?php

namespace App\Http\Repositories\Password;

use App\Http\Models\Password\PasswordModel;
use App\Http\Repositories\Folder\FolderRepository;
use App\Models\Password;
use Illuminate\Support\Facades\Auth;

use App\Exceptions\Folder\FolderNotFoundException;
use App\Exceptions\Password\PasswordAlreadyExistsException;
use App\Exceptions\Password\PasswordNotFoundException;

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

    public function delete(int $passwordId) : bool {
        $model = $this->retrievePasswordFromId($passwordId);
        if (!$model) throw new PasswordNotFoundException();
        $model->delete();
        return true;
    }

    /*
      $passwords = PasswordModel[]
    */
    public function createMany(array $passwords) : array {

        $createdModels = [];
        $updatedModels = [];

        $names = array_map(function ($password) {
            return $password->name;
        }, $passwords);

        $passwordModals = Auth::user()->passwords()->whereIn('name', $names)->get();

        foreach ($passwords as $password) {
            $model = $passwordModals->where('name', $password->name)->first();
            $createdModel = array_key_exists($password->name, $createdModels) ? $createdModels[$password->name] : null;

            if (is_null($model) && is_null($createdModel)) {
                $model = $this->create($password);
                $createdModels[$password->name] = $model;
                continue;
            }

            $model = $model ?? $createdModel;
            $password->setId($model->id);
            $model = $this->update($password, $model);
            $updatedModels[$model->name] = $model;
        }

        return [ 'created' => $createdModels, 'updated' => $updatedModels ];
    }

    public function create(PasswordModel $password) : Password {
        if ($this->checkIfExists($password->name)) throw new PasswordAlreadyExistsException();

        $folderRepository = new FolderRepository();
        $model = new Password();
        $model->user_id = Auth::user()->id;
        $model->name = $password->name;
        if ($password->url) $model->url = $password->url;
        if ($password->login) $model->login = $password->login;
        if ($password->password) $model->password = $password->password;
        if ($password->description) $model->description = $password->description;

        if (!is_null($password->folder_name)) {
            if (is_null($password->folder_id)) {
                $folder = $folderRepository->create($password->folder_name);
            }

            if (!is_null($password->folder_id)) {
                $folder = $folderRepository->retrieveFolderFromId($password->folder_id);
                if (!$folder) throw new FolderNotFoundException();
            }

            $model->folder_id = $folder->id;
        }

        $model->save();
        return $model;
    }

    public function update(PasswordModel $password, Password $model = null) : Password {
        $model = is_null($model) ? $this->retrievePasswordFromId($password->id) : $model;

        if (is_null($password) || is_null($password->id)) throw new PasswordNotFoundException();

        if ($password->name) {
            if ($this->checkIfExists($password->name, $model->id)) throw new PasswordAlreadyExistsException();
            $model->name = $password->name;
        }

        $folderRepository = new FolderRepository();
        $model->url = $password->url;
        $model->login = $password->login;
        $model->password = $password->password;
        $model->description = $password->description;

        $currentFolder = $model->folder;

        if (!is_null($password->folder_id) || !is_null($password->folder_name)) {
            if (is_null($password->folder_id)) {
                $folder = $folderRepository->create($password->folder_name);
            }

            if (!is_null($password->folder_id)) {
                $folder = $folderRepository->retrieveFolderFromId($password->folder_id);
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
        return $model->fresh();
    }
}
