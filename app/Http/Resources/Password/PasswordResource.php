<?php

namespace App\Http\Resources\Password;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Crypt;

class PasswordResource extends JsonResource
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
            'type' => 'passwords',
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'url' => (string) $this->url,
            'login' => (string) $this->login ? Crypt::decryptString($this->login) : '',
            'password' => (string) $this->password ? Crypt::decryptString($this->password) : '',
            'folderId' => (string) $this->folder_id,
            'description' => (string) $this->description,
            'lastUpdate' => (string) $this->updated_at
        ];
    }
}
