<?php

namespace App\Http\Resources\Folder;

use Illuminate\Http\Resources\Json\JsonResource;

class FolderResource extends JsonResource
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
            'id' => (string) $this->id,
            'name' => (string) $this->name,
        ];
    }
}
