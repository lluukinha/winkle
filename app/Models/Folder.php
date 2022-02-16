<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;

    public function user() {
        return $this->hasOne(User::class);
    }

    public function passwords() {
        return $this->hasMany(Password::class);
    }
}
