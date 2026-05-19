<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\SymbolTable;

/**
 * @todo implement that interface may not have properties and
 * methods must always be public
 */
class Checker
{
    public array $checkers = [];

    public function __construct(public readonly SymbolTable $table)
    {
        $this->checkers = (new PassDiscovery())->discover(
            __DIR__ . '/Checker',
            \PHireScript\Compiler\Checker\Checker::class,
        );
    }

    public function check(Program $ast): void
    {
        foreach ($this->checkers as $check) {
            if ($check->mustCheck($ast)) {
                $check->check($ast, $this);
            }
        }
    }
}
