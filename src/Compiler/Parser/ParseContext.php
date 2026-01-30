<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser;

use PHireScript\Compiler\Parser\Managers\ContextManager;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\VariableManager;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

class ParseContext
{
    public function __construct(
        public VariableManager $variables,
        public Program $program,
        public ParserDispatcher $emitter,
        public TokenManager $tokenManager,
        public ContextManager $context,
    ) {
    }
}
