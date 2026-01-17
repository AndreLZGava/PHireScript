<?php

namespace PHPScript\Classes;

use PHPScript\Classes\UserCredentials;
interface Authenticator {
    public function authenticate(): bool;

    public function logout(): void;

}

