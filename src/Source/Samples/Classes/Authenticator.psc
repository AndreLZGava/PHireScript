<?php

namespace PHireScript\Classes;

use PHireScript\Classes\UserCredentials;
interface Authenticator {
    public function authenticate(): bool;

    public function logout(): void;

}

