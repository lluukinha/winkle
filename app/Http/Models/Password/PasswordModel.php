<?php

namespace App\Http\Models\Password;

use Illuminate\Support\Facades\Crypt;

class PasswordModel {

    public $id = null;
    public $name = null;
    public $url = null;
    public $login = null;
    public $password = null;
    public $description = null;
    public $folder_id = null;
    public $folder_name = null;

    public function __construct(
        string $name,
        string $url = null,
        string $login = null,
        string $password = null,
        string $description = null,
        string $folder_id = null,
        string $folder_name = null,
    ) {
        $this->name = $name;
        $this->url = $url;
        $this->description = $description;
        $this->folder_id = $folder_id;
        $this->folder_name = $folder_name;

        $this->login = !is_null($login) ? $this->encrypt($login) : null;
        $this->password = !is_null($password) ? $this->encrypt($password) : null;
    }

    public function setId(int $id) {
        $this->id = $id;
    }

    public function encrypt(string $text) : string {
        return Crypt::encryptString($text);
    }

    public function decrypt(string $text) {
        return Crypt::decryptString($text);
    }
}
