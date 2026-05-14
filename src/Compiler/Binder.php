<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Binder\Declaration\ClassBinder;
use PHireScript\Compiler\Binder\Declaration\Class\MagicMethodDeclarationBinder;
use PHireScript\Compiler\Binder\Declaration\Interface\MethodDeclarationBinder;
use PHireScript\Compiler\Binder\Declaration\PropertyBinder;
use PHireScript\Compiler\Binder\Declaration\InterfaceBinder;
use PHireScript\Compiler\Binder\Root\ProgramBinder;
use PHireScript\Compiler\Binder\Signatures\ModifiersBinder;
use PHireScript\SymbolTable;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\DependencyStatementNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Helper\TypeResolver;

class Binder
{
    private Program $program;
    public array $binders = [];
    public function __construct(private readonly SymbolTable $globalTable)
    {
        $this->binders = [
            new ProgramBinder(),
            new InterfaceBinder(),
            new ClassBinder(),
            new MagicMethodDeclarationBinder(),
            new MethodDeclarationBinder(),
            new PropertyBinder(),
            new ModifiersBinder(),
        ];
    }

    public function bind(Program $program)
    {
        $this->program = $program;

        foreach ($this->binders as $bind) {
            if ($bind->mustBind($program)) {
                $bind->bind($program, $this);
            }
        }

        foreach ($program->statements as $node) {
            if (
                $node instanceof ClassNode ||
                $node instanceof InterfaceNode
            ) {
                $this->globalTable->registerTypeDefinition($node->name, $node);
            }
        }

        foreach ($program->statements as $node) {
            if (
                $node instanceof ClassNode ||
                $node instanceof InterfaceNode
            ) {
                $this->bindClassBody($node);
            }
        }

        return $program;
    }

    protected function bindClassBody(ClassNode|InterfaceNode $class)
    {
        $this->bindWithToBody($class);
        foreach ($class->body->children as $member) {
            if ($member instanceof PropertyNode) {
                $this->resolvePropertyTypes($member);
            }

            if ($member instanceof MethodDeclarationNode) {
                $this->resolvePropertyTypeForMethods($member);
            }
        }
    }

    protected function bindWithToBody($class)
    {
        if (isset($class->with)) {
            array_unshift($class->body->children, $class->with);
        }
    }

    protected function resolvePropertyTypeForMethods(MethodDeclarationNode $prop)
    {
        foreach ($prop->parameters->params as $propertyNode) {
            $this->resolvePropertyTypes($propertyNode);
        }
    }

    protected function resolvePropertyTypes(PropertyNode|ParamArgumentNode $prop)
    {
        $resolved = [];
        foreach ($prop->types as $type) {
            $resolved[] = $this->categorizeType($type);
        }
        $prop->resolvedTypeInfo = $resolved;
    }

    protected function categorizeType(string $typeName): array
    {
        $info = TypeResolver::classify($typeName);
        if ($info !== null) {
            return $info;
        }

        if ($this->verifyUses($typeName)) {
            return ['category' => 'custom', 'name' => $typeName];
        }

        $isRegistered = $this->globalTable->getTypeDefinition($typeName);
        return [
            'category' => $isRegistered ? 'custom' : 'unknown',
            'name' => $typeName,
        ];
    }

    private function verifyUses(string $typeName): bool
    {

        $uses = [];

        foreach ($this->program->statements as $statement) {
            if ($statement instanceof UseNode) {
                foreach ($statement->packages as $package) {
                    if ($package instanceof DependencyStatementNode) {
                        $usingPackage = \explode('.', $package->package);
                        $namedPackage  = !empty($package->alias) ?
                            $package->alias :
                            \end($usingPackage);
                        $uses[$namedPackage] = $package;
                    }
                }
            }
        }

        if (\array_key_exists($typeName, $uses)) {
            return true;
        }

        return false;
    }
}
