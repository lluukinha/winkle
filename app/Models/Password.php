<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Password extends Model
{
    use HasFactory;

    public function user() {
        return $this->hasOne(User::class);
    }

    public function folder() {
        return $this->belongsTo(Folder::class);
    }
}
