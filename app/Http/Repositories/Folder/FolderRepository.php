<?php

namespace App\Http\Repositories\Folder;

use App\Exceptions\Folder\FolderAlreadyExistsException;
use App\Exceptions\Folder\FolderHasPasswordsException;
use App\Exceptions\Folder\FolderNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;

class FolderRepository {

    public function retrieveFolderFromName(string $name) : Folder | null {
        return Auth::user()->folders()
            ->where('name', strtoupper($name))
            ->first();
    }

    public function retrieveFolderFromId(int $folderId) : Folder | null {
        return Auth::user()->folders()->find($folderId);
    }

    private function checkIfExists(string $nameToCheck, int $currentId = null) : bool {
        $list = Auth::user()->folders();
        if (!is_null($currentId)) $list = $list->where('id', '<>', $currentId);
        return $list->where('name', $nameToCheck)->exists();
    }

    public function forceCreate(string $name) : Folder {
        $exists = $this->checkIfExists($name);
        if ($exists) throw new FolderAlreadyExistsException();
        return $this->create($name);
    }

    public function create(string $name) : Folder {
        $findFolder = $this->retrieveFolderFromName($name);
        if (!is_null($findFolder)) return $findFolder;

        $folder = new Folder();
        $folder->user_id = Auth::user()->id;
        $folder->name = strtoupper($name);
        $folder->save();
        return $folder;
    }

    public function update(int $id, string $newName) : Folder {
        $model = $this->retrieveFolderFromId($id);
        if (is_null($model)) throw new FolderNotFoundException();

        $exists = $this->checkIfExists($newName, $id);
        if ($exists) throw new FolderAlreadyExistsException();

        $model->name = strtoupper($newName);
        $model->save();
        return $model->fresh();
    }

    public function delete(int $id) : bool {
        $folder = $this->retrieveFolderFromId($id);

        if (is_null($folder)) throw new FolderNotFoundException();
        if ($folder->passwords()->count() > 0) throw new FolderHasPasswordsException();

        $folder->delete();
        return true;
    }
}
