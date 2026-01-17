<?php

namespace PHireScript\Classes;

use PHireScript\Classes\UserCredentials;
use PHireScript\Classes\User as UserAccess;
use Symfony\Component\DependencyInjection\Loader\GlobFileLoader;
class AuthenticatorClass {
    public function authenticate(): bool {
        return true;
    }

    public function logout(): void {
        return ;
    }

    public function returnNull(): null {
        return Null;
    }

    public function returnStringSingle(): string {
        return 'single quotes';
    }

    public function returnStringDouble(): string {
        return 'single quotes';
    }

    public function returnFloat(): float {
        return 15.2;
    }

    public function returnInt(): int {
        return 10;
    }

    public function returnArrayEmpty(): array {
        return [];
    }

    public function returnObject(): object {
            // this are not compiling into a object

        return ;
    }

    public function returnArrayComplete(): array {
            // This gets loop, todo implement support to it

            // return ['example' => ['another', 'array']]

    }

}
