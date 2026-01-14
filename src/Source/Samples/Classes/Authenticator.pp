<?php

namespace PHPScript;


interface Authenticator {
    public function authenticate(): bool;
    public function logout(): void;
}
