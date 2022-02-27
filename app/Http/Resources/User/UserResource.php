<?php

namespace App\Http\Resources\User;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'type' => 'users',
            'id' => (string) $this->id,
            'name' => (string) $this->name,
            'email' => (string) $this->email,
            'emailVerified' => (bool) !is_null($this->email_verified_at),
            'canUpdateMasterPassword' => (bool) $this->canUpdateMasterPassword(),
            'expirationDate' => $this->expirationDate(),
            'lastUpdate' => $this->updated_at,
            'createdAt' => $this->created_at,
        ];
    }
}
