<?php

namespace App\Http\Controllers\Folder;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Http\Repositories\Folder\FolderRepository;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\ApiExceptions\Http422;
use App\Exceptions\Folder\FolderAlreadyExistsException;
use App\Exceptions\Folder\FolderHasPasswordsException;
use App\Exceptions\Folder\FolderNotFoundException;
use App\Http\Requests\Folder\CreateOrUpdateFolderRequest;
use App\Http\Resources\Folder\FolderResource;

class FolderController extends Controller
{
    public function list() {
        $folders = Auth::user()->folders()->get();
        return FolderResource::collection($folders);
    }

    public function create(CreateOrUpdateFolderRequest $request) {
        try {
            $attributes = $request->validated();
            $repository = new FolderRepository();
            $folder = $repository->forceCreate($attributes['name']);
            return new FolderResource($folder);
        } catch (FolderAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'folder-name-already-exists');
        }
    }

    public function update(CreateOrUpdateFolderRequest $request, int $id) {
        try {
            $attributes = $request->validated();
            $repository = new FolderRepository();
            $folder = $repository->update($id, $attributes['name']);
            return new FolderResource($folder);
        } catch (FolderAlreadyExistsException $e) {
            throw Http422::makeForField('name', 'already-exists');
        } catch (FolderNotFoundException $e) {
            throw Http404::makeForField('folder', 'not-found');
        }
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
