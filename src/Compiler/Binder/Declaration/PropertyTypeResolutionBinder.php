<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Binder\Declaration;

use PHireScript\Compiler\Binder as CompilerBinder;
use PHireScript\Compiler\Binder\Binder;
use PHireScript\Compiler\CompilerPass;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\DependencyStatementNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Node;
use PHireScript\Helper\TypeResolver;

#[CompilerPass(order: 10)]
class PropertyTypeResolutionBinder implements Binder
{
    public function mustBind(Node $node): bool
    {
        return $node instanceof PropertyNode || $node instanceof ParamArgumentNode;
    }

    public function bind(Node $node, CompilerBinder $binder): void
    {
        $resolved = [];
        foreach ($node->types as $type) {
            $resolved[] = $this->categorizeType($type, $binder);
        }
        $node->resolvedTypeInfo = $resolved;
    }

    private function categorizeType(string $typeName, CompilerBinder $binder): array
    {
        $info = TypeResolver::classify($typeName);
        if ($info !== null) {
            return $info;
        }

        if ($this->verifyUses($typeName, $binder)) {
            return ['category' => 'custom', 'name' => $typeName];
        }

        $isRegistered = $binder->globalTable->getTypeDefinition($typeName);
        return [
            'category' => $isRegistered ? 'custom' : 'unknown',
            'name' => $typeName,
        ];
    }

    private function verifyUses(string $typeName, CompilerBinder $binder): bool
    {
        $uses = [];

        foreach ($binder->program->statements as $statement) {
            if ($statement instanceof UseNode) {
                foreach ($statement->packages as $package) {
                    if ($package instanceof DependencyStatementNode) {
                        $usingPackage = \explode('.', $package->package);
                        $namedPackage = !empty($package->alias)
                            ? $package->alias
                            : \end($usingPackage);
                        $uses[$namedPackage] = $package;
                    }
                }
            }
        }

        return \array_key_exists($typeName, $uses);
    }
}
