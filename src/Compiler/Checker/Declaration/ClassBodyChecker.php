<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;

#[CompilerPass(order: 6)]
class ClassBodyChecker extends Checker
{
    public function mustCheck(Node $node): bool
    {
        return $node instanceof ClassNode;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof ClassNode);

        foreach ($node->body !== null ? $node->body->children : [] as $member) {
            if (!($member instanceof PropertyNode)) {
                continue;
            }

            $propertyName = $member->name;

            if ($node->readOnly && $member->value) {
                throw new \Exception(
                    "Semantic error in property '{$propertyName}': " .
                        "Readonly classes or immutable object its not" .
                        " allowed to define a default value!"
                );
            }

            if (
                !\in_array('abstract', $node->modifiers, true) &&
                \in_array('abstract', $member->modifiers, true)
            ) {
                throw new \Exception(
                    "Semantic error in property '{$propertyName}': " .
                        "Abstract properties are allowed only in abstract classes"
                );
            }
        }
    }
}
