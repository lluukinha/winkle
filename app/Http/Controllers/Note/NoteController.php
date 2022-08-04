<?php

namespace App\Http\Controllers\Note;

use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Auth;

use App\Exceptions\ApiExceptions\Http404;
use App\Exceptions\Note\NoteNotFoundException;
use App\Http\Repositories\Note\NoteRepository;
use App\Http\Requests\Note\NoteRequest;
use App\Http\Resources\Note\NoteResource;

class NoteController extends Controller
{
    public function list() {
        $notes = Auth::user()->notes;
        return NoteResource::collection($notes);
    }

    public function create(NoteRequest $request) {
        $attributes = $request->validated();
        $repository = new NoteRepository();
        $note = $repository->create($attributes["note"]);
        return new NoteResource($note);
    }

    public function update(NoteRequest $request, int $noteId) {
        try {
            $attributes = $request->validated();
            $repository = new NoteRepository();
            $note = $repository->update($noteId, $attributes["note"]);
            return new NoteResource($note);
        } catch (NoteNotFoundException $e) {
            throw Http404::makeForField('note', 'not-found');
        }
    }

    public function delete(int $noteId) {
        try {
            $repository = new NoteRepository();
            $isDeleted = $repository->delete($noteId);
            return response()->json($isDeleted);
        } catch (NoteNotFoundException $e) {
            throw Http404::makeForField('note', 'not-found');
        }
    }
}
