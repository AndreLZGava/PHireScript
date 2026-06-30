<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\OOP;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\Type\PhpTypeResolver;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;

class GetterSetterEmitter
{
    private const INDENT = '    ';

    public function emit(ClassBodyNode $node, EmitContext $ctx): string
    {
        $explicitMethods = $this->collectExplicitMethodNames($node);
        $types = new PhpTypeResolver();
        $code = '';

        foreach ($node->children as $child) {
            if (!($child instanceof PropertyNode)) {
                continue;
            }

            if ($child->getter !== null) {
                $methodName = 'get' . \ucfirst($child->name);
                if (!\in_array($methodName, $explicitMethods, true)) {
                    $code .= $this->emitGetter($child, $child->getter, $types, $ctx);
                }
            }

            if ($child->setter !== null) {
                $methodName = 'set' . \ucfirst($child->name);
                if (!\in_array($methodName, $explicitMethods, true)) {
                    $code .= $this->emitSetter($child, $child->setter, $types, $ctx);
                }
            }
        }

        return $code;
    }

    private function emitGetter(
        PropertyNode $prop,
        string $visibility,
        PhpTypeResolver $types,
        EmitContext $ctx
    ): string {
        $name       = \ucfirst($prop->name);
        $phpType    = $types->phpType($prop);
        $returnType = $phpType ? ': ' . $phpType : '';
        $indent     = self::INDENT;

        return "{$indent}{$visibility} function get{$name}(){$returnType}\n"
            . "{$indent}{\n"
            . "{$indent}{$indent}return \$this->{$prop->name};\n"
            . "{$indent}}\n\n";
    }

    private function emitSetter(
        PropertyNode $prop,
        string $visibility,
        PhpTypeResolver $types,
        EmitContext $ctx
    ): string {
        $name      = \ucfirst($prop->name);
        $phpType   = $types->phpType($prop);
        $paramType = $phpType ? $phpType . ' ' : '';
        $body      = $types->assignment($prop, $ctx->uses);
        $indent    = self::INDENT;

        return "{$indent}{$visibility} function set{$name}({$paramType}\${$prop->name}): void\n"
            . "{$indent}{\n"
            . "{$indent}{$indent}{$body}\n"
            . "{$indent}}\n\n";
    }

    /** @return string[] */
    private function collectExplicitMethodNames(ClassBodyNode $node): array
    {
        $names = [];
        foreach ($node->children as $child) {
            if ($child instanceof MethodDeclarationNode) {
                $names[] = $child->name;
            }
        }
        return $names;
    }
}
