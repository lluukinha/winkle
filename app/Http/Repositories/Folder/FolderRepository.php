<?php

namespace App\Http\Repositories\Folder;

use App\Exceptions\Folder\FolderHasPasswordsException;
use App\Exceptions\Folder\FolderNotFoundException;
use Illuminate\Support\Facades\Auth;
use App\Models\Folder;

class FolderRepository {

    public function retrieveFolderFromName(string $name) : Folder | null {
        return Auth::user()->folders()
            ->where([ 'name' => strtoupper($name), 'model' => 'passwords' ])
            ->first();
    }

    public function retrieveFolderFromId(int $folderId) : Folder | null {
        return Auth::user()->folders()
            ->where([ 'id' => $folderId, 'model' => 'passwords' ])
            ->first();
    }

    public function create(string $name) : Folder {
        $findFolder = $this->retrieveFolderFromName($name);
        if (!is_null($findFolder)) return $findFolder;

        $folder = new Folder();
        $folder->user_id = Auth::user()->id;
        $folder->model = "passwords";
        $folder->name = strtoupper($name);
        $folder->save();
        return $folder;
    }

    public function delete(int $id) : bool {
        $folder = $this->retrieveFolderFromId($id);

        if (is_null($folder)) throw new FolderNotFoundException();
        if ($folder->passwords()->count() > 0) throw new FolderHasPasswordsException();

        $folder->delete();
        return true;
    }
}
