<?php


namespace PHireScript\Classes;


use PHireScript\Classes\UserCredentials;


interface Authenticator {
    public function authenticate(UserCredentials $credentials): bool;

    public function logout(): void;

}


