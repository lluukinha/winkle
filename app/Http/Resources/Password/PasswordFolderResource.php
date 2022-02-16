<?php

namespace App\Http\Resources\Password;

use Illuminate\Http\Resources\Json\JsonResource;

class PasswordFolderResource extends JsonResource
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
            'type' => 'password_folders',
            'id' => (string) $this->id,
            'name' => (string) $this->name,
        ];
    }
}
