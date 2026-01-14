<?php

namespace PHPScript;

use PHPScript\Runtime\Types\SuperTypes\Email;

readonly class User {
    public int $id;
    public string $username;
    public string $email;
    public bool $isAdmin;
    public null|array $metadata;

    public function __construct(
        int $id,
        string $username,
        string $email,
        bool $isAdmin,
        null|array $metadata = null
    ) {
        $this->id = $id;
        $this->username = $username;
        $this->email = Email::cast($email);
        $this->isAdmin = $isAdmin;
        $this->metadata = $metadata;
    }
}
