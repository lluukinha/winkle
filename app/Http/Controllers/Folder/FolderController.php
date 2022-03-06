<?php

namespace App\Http\Controllers\Folder;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Http\Repositories\Folder\FolderRepository;
use App\Http\Resources\Password\PasswordFolderResource;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\Folder\FolderHasPasswordsException;
use App\Exceptions\Folder\FolderNotFoundException;

class FolderController extends Controller
{
    public function list() {
        $folders = Auth::user()->folders()->where('model', 'passwords')->get();
        return PasswordFolderResource::collection($folders);
    }

    public function delete($id) {
        try {
            $repository = new FolderRepository();
            $isDeleted = $repository->delete($id);
            return response()->json($isDeleted);
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        } catch (FolderHasPasswordsException $e) {
            throw Http422::makeForField('folder', 'folder-has-passwords');
        }
    }
}
