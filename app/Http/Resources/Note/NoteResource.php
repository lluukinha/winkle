<?php

namespace App\Http\Resources\Note;

use App\Http\Resources\Folder\FolderResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class NoteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'type' => 'notes',
            'id' => (string) $this->id,
            'note' => (string) $this->note,
        ];
    }
}
