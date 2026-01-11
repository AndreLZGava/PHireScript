<?php

namespace PHPScript;


interface UserInterface {
    public function save(array $data): bool;
    public function delete(): void;
    public function getCompleteUserName(): string|null;
}
