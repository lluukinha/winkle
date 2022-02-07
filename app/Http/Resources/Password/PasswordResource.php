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
            'login' => (string) $this->login,
            'password' => (string) Crypt::decryptString($this->password),
            'description' => (string) $this->description,
        ];
    }
}
