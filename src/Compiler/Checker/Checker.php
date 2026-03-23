<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;

interface Checker
{
    public function mustCheck(Node $node): bool;
    public function check(Node $node, CompilerChecker $checker): void;
}
