<?php

namespace PHPScript\Compiler\Scanner;

class PropertyDefinition extends Node {
    public string $name;
    public ?string $type = null;
    public array $modifiers = [];
    public ?string $defaultValue = null;
}
