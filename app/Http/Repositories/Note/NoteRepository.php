<?php

namespace App\Http\Repositories\Note;

use App\Models\Note;
use Illuminate\Support\Facades\Auth;
use App\Exceptions\Note\NoteNotFoundException;

class NoteRepository {

    private function retrieveNoteFromId(int $id) : Note {
        $note = Auth::user()->notes->find($id);
        if (!$note) throw new NoteNotFoundException();
        return $note;
    }

    public function retrieveList() {
        return Auth::user()->notes;
    }

    public function delete(int $noteId) : bool {
        $model = $this->retrieveNoteFromId($noteId);
        $model->delete();
        return true;
    }

    public function create(string $note) : Note {
        $model = new Note();
        $model->user_id = Auth::user()->id;
        $model->note = $note;
        $model->save();
        return $model;
    }

    public function update(int $noteId, string $newNote) : Note {
        $model = $this->retrieveNoteFromId($noteId);
        $model->note = $newNote;
        $model->save();
        return $model->fresh();
    }
}
