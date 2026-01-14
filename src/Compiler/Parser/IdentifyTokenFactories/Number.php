<?php

declare(strict_types=1);

namespace PHPScript\Compiler\Parser\IdentifyTokenFactories;

use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\Node;
use PHPScript\Compiler\Program;
use PHPScript\Helper\Debug\Debug;

class Number extends GlobalFactory
{
    public function process(Program $program): ?Node
    {
        Debug::show($this->tokenManager->getCurrentToken());
        exit;
        $node = new GlobalStatement();
        $node->code = trim((string) $this->tokenManager->getCurrentToken()['value']);
        return $node;
    }
}
