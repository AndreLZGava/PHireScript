<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\SymbolTable;

class Binder
{
    public Program $program;
    public array $binders = [];

    public function __construct(public readonly SymbolTable $globalTable)
    {
        $this->binders = (new PassDiscovery())->discover(
            __DIR__ . '/Binder',
            \PHireScript\Compiler\Binder\Binder::class,
        );
    }

    public function bind(Program $program)
    {
        $this->program = $program;

        foreach ($this->binders as $bind) {
            if ($bind->mustBind($program)) {
                $bind->bind($program, $this);
            }
        }

        return $program;
    }
}
