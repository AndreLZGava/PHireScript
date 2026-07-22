<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Checker\Declaration;

use PHireScript\Compiler\Checker as CompilerChecker;
use PHireScript\Compiler\Checker\Checker;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\AttributeUsageNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\PackageDependencyNode;
use PHireScript\Compiler\Program;
use PHireScript\Runtime\Exceptions\CompileException;

#[CompilerPass(order: 7)]
class AttributeUsageChecker extends Checker
{
    private const PHP_BUILTINS = [
        'Attribute',
        'AllowDynamicProperties',
        'Deprecated',
        'Override',
        'ReturnTypeWillChange',
        'SensitiveParameter',
    ];

    public function mustCheck(Node $node): bool
    {
        return $node instanceof Program;
    }

    public function check(Node $node, CompilerChecker $checker): void
    {
        assert($node instanceof Program);

        $imported = $this->collectImportedNames($node);

        foreach ($node->statements as $statement) {
            if (!($statement instanceof ClassNode)) {
                continue;
            }

            foreach ($statement->attributes as $attr) {
                $this->validateAttr($attr, $imported);
            }

            foreach ($statement->body !== null ? $statement->body->children : [] as $member) {
                if (!($member instanceof PropertyNode)) {
                    continue;
                }
                foreach ($member->attributes as $attr) {
                    $this->validateAttr($attr, $imported);
                }
            }
        }
    }

    /** @return array<string> short names available in this file */
    private function collectImportedNames(Program $program): array
    {
        $names = [];
        foreach ($program->statements as $statement) {
            if (!($statement instanceof UseNode)) {
                continue;
            }
            foreach ($statement->packages as $pkg) {
                if (!($pkg instanceof PackageDependencyNode)) {
                    continue;
                }
                if ($pkg->alias !== null) {
                    $names[] = $pkg->alias;
                } else {
                    $parts = \explode('.', $pkg->package);
                    $names[] = \end($parts);
                }
            }
        }
        return $names;
    }

    private function validateAttr(mixed $attr, array $imported): void
    {
        if (!($attr instanceof AttributeUsageNode)) {
            return;
        }
        if (\in_array($attr->name, self::PHP_BUILTINS, true)) {
            return;
        }
        if (\in_array($attr->name, $imported, true)) {
            return;
        }
        throw new CompileException(
            "Attribute '@{$attr->name}' is not declared. " .
            "Either declare it as 'attribute {$attr->name} { ... }' and import it with 'use', " .
            "or use a built-in PHP attribute.",
            $attr->token->line,
            $attr->token->column,
        );
    }
}
