<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Checker\Declaration\Class\MagicMethodsChecker;
use PHireScript\Compiler\Checker\Declaration\Class\MethodReturnChecker;
use PHireScript\Compiler\Checker\Declaration\ClassBodyChecker;
use PHireScript\Compiler\Checker\Declaration\ClassChecker;
use PHireScript\Compiler\Checker\Expression\MethodConsumptionChecker;
use PHireScript\Compiler\Checker\Root\ProgramChecker;
use PHireScript\Compiler\Checker\Expression\Types\QueueChecker;
use PHireScript\SymbolTable;

/**
 * @todo implement that interface may not have properties and
 * methods must always be public
 */
class Checker
{
    public ?SymbolTable $table = null;
    public array $checkers = [];

    public function __construct()
    {
        $this->checkers = [
            new QueueChecker(),
            new MethodConsumptionChecker(),
            new MagicMethodsChecker(),
            new ProgramChecker(),
            new ClassChecker(),
            new ClassBodyChecker(),
            new MethodReturnChecker(),
        ];
    }

    public function check(Program $ast, SymbolTable $table)
    {
        $this->table = $table;

        foreach ($this->checkers as $check) {
            if ($check->mustCheck($ast)) {
                $check->check($ast, $this);
            }
        }
    }
}
