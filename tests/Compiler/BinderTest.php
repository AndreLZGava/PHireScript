<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Binder;
use PHireScript\Compiler\Program;
use PHireScript\SymbolTable;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\DependencyStatementNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Signatures\ParamArgumentNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Tests\Compiler\Helpers\BinderSpy;

class BinderTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    private function makeToken(string $value = 'test', string $type = 'T_KEYWORD'): Token
    {
        return new Token($type, $value, 1, 1);
    }

    private function makeProgram(array $statements = []): Program
    {
        $program = new Program($this->makeToken('program'));
        $program->statements = $statements;
        return $program;
    }

    private function makeClassNode(string $name = 'Foo'): ClassNode
    {
        $token     = new Token('T_KEYWORD', 'class', 1, 1);
        $classNode = new ClassNode($token);
        $classNode->name = $name;

        $bodyToken       = new Token('T_SYMBOL', '{', 1, 5);
        $classNode->body = new ClassBodyNode($bodyToken, $name, 'class');

        return $classNode;
    }

    private function makeInterfaceNode(string $name = 'Bar'): InterfaceNode
    {
        $token = new Token('T_KEYWORD', 'interface', 1, 1);
        $node  = new InterfaceNode($token);
        $node->name = $name;

        $bodyToken  = new Token('T_SYMBOL', '{', 1, 10);
        $node->body = new InterfaceBodyNode($bodyToken, $name);

        return $node;
    }

    private function makeSpyWithEmptyProgram(): BinderSpy
    {
        $spy = new BinderSpy(new SymbolTable());
        $spy->bind($this->makeProgram()); // initialises $this->program
        return $spy;
    }

    // -------------------------------------------------------------------------
    // bind() returns the same Program instance
    // -------------------------------------------------------------------------

    public function testBindReturnsSameProgram(): void
    {
        $binder  = new Binder(new SymbolTable());
        $program = $this->makeProgram();
        $result  = $binder->bind($program);
        $this->assertSame($program, $result);
    }

    // -------------------------------------------------------------------------
    // Type registration in SymbolTable
    // -------------------------------------------------------------------------

    public function testBindRegistersClassNodeInSymbolTable(): void
    {
        $table     = new SymbolTable();
        $binder    = new Binder($table);
        $classNode = $this->makeClassNode('MyClass');

        $binder->bind($this->makeProgram([$classNode]));

        $this->assertSame($classNode, $table->getTypeDefinition('MyClass'));
    }

    public function testBindRegistersInterfaceNodeInSymbolTable(): void
    {
        $table     = new SymbolTable();
        $binder    = new Binder($table);
        $ifaceNode = $this->makeInterfaceNode('MyInterface');

        $binder->bind($this->makeProgram([$ifaceNode]));

        $this->assertSame($ifaceNode, $table->getTypeDefinition('MyInterface'));
    }

    public function testBindRegistersMultipleTypesInSymbolTable(): void
    {
        $table  = new SymbolTable();
        $binder = new Binder($table);

        $class1 = $this->makeClassNode('Alpha');
        $class2 = $this->makeClassNode('Beta');
        $iface  = $this->makeInterfaceNode('Gamma');

        $binder->bind($this->makeProgram([$class1, $class2, $iface]));

        $this->assertSame($class1, $table->getTypeDefinition('Alpha'));
        $this->assertSame($class2, $table->getTypeDefinition('Beta'));
        $this->assertSame($iface, $table->getTypeDefinition('Gamma'));
    }

    public function testBindDoesNotRegisterNonClassOrInterfaceNodes(): void
    {
        $table  = new SymbolTable();
        $binder = new Binder($table);

        $useToken = new Token('T_KEYWORD', 'use', 1, 1);
        $useNode  = new UseNode($useToken, []);
        $binder->bind($this->makeProgram([$useNode]));

        $this->assertNull($table->getTypeDefinition('use'));
    }

    // -------------------------------------------------------------------------
    // categorizeType — primitives
    // -------------------------------------------------------------------------

    public function testCategorizeTypeString(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('String');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('string', $result['native']);
    }

    public function testCategorizeTypeInt(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Int');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('int', $result['native']);
    }

    public function testCategorizeTypeFloat(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Float');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('float', $result['native']);
    }

    public function testCategorizeTypeBool(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Bool');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('bool', $result['native']);
    }

    public function testCategorizeTypeVoid(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Void');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('void', $result['native']);
    }

    public function testCategorizeTypeNull(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Null');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('null', $result['native']);
    }

    public function testCategorizeTypeMixed(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Mixed');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('mixed', $result['native']);
    }

    public function testCategorizeTypeAnyMapsToMixed(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Any');
        $this->assertSame('primitive', $result['category']);
        $this->assertSame('mixed', $result['native']);
    }

    // -------------------------------------------------------------------------
    // categorizeType — MetaTypes
    // -------------------------------------------------------------------------

    public function testCategorizeTypeDate(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Date');
        $this->assertSame('metatype', $result['category']);
        $this->assertStringContainsString('Date', $result['class']);
    }

    public function testCategorizeTypeCurrency(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Currency');
        $this->assertSame('metatype', $result['category']);
    }

    // -------------------------------------------------------------------------
    // categorizeType — SuperTypes
    // -------------------------------------------------------------------------

    public function testCategorizeTypeEmail(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Email');
        $this->assertSame('supertype', $result['category']);
        $this->assertStringContainsString('Email', $result['class']);
    }

    public function testCategorizeTypeUuid(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Uuid');
        $this->assertSame('supertype', $result['category']);
    }

    public function testCategorizeTypeUrl(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('Url');
        $this->assertSame('supertype', $result['category']);
    }

    // -------------------------------------------------------------------------
    // categorizeType — registered custom type
    // -------------------------------------------------------------------------

    public function testCategorizeTypeRegisteredCustomType(): void
    {
        $table     = new SymbolTable();
        $classNode = $this->makeClassNode('UserRepository');
        $table->registerTypeDefinition('UserRepository', $classNode);

        $spy = new BinderSpy($table);
        $spy->bind($this->makeProgram());

        $result = $spy->publicCategorizeType('UserRepository');
        $this->assertSame('custom', $result['category']);
        $this->assertSame('UserRepository', $result['name']);
    }

    // -------------------------------------------------------------------------
    // categorizeType — unknown type
    // -------------------------------------------------------------------------

    public function testCategorizeTypeUnregisteredIsUnknown(): void
    {
        $spy    = $this->makeSpyWithEmptyProgram();
        $result = $spy->publicCategorizeType('SomeMadeUpType');
        $this->assertSame('unknown', $result['category']);
        $this->assertSame('SomeMadeUpType', $result['name']);
    }

    // -------------------------------------------------------------------------
    // categorizeType — use imports (verifyUses)
    // -------------------------------------------------------------------------

    public function testCategorizeTypeWithMatchingUseImportReturnsCustom(): void
    {
        $spy = new BinderSpy(new SymbolTable());

        $useToken = new Token('T_KEYWORD', 'use', 1, 1);
        $depToken = new Token('T_IDENTIFIER', 'App.Services.UserService', 1, 5);
        $dep      = new DependencyStatementNode($depToken, 'App.Services.UserService');
        $useNode  = new UseNode($useToken, [$dep]);

        $spy->bind($this->makeProgram([$useNode]));

        $result = $spy->publicCategorizeType('UserService');
        $this->assertSame('custom', $result['category']);
    }

    public function testCategorizeTypeWithAliasUsesAliasNotLastSegment(): void
    {
        $spy = new BinderSpy(new SymbolTable());

        $useToken = new Token('T_KEYWORD', 'use', 1, 1);
        $depToken = new Token('T_IDENTIFIER', 'App.Services.UserService', 1, 5);
        $dep      = new DependencyStatementNode($depToken, 'App.Services.UserService', 'US');
        $useNode  = new UseNode($useToken, [$dep]);

        $spy->bind($this->makeProgram([$useNode]));

        // The alias 'US' is resolvable, but the original segment 'UserService' is not.
        $this->assertSame('custom', $spy->publicCategorizeType('US')['category']);
        $this->assertSame('unknown', $spy->publicCategorizeType('UserService')['category']);
    }

    public function testCategorizeTypeUnknownWhenNoUseMatchesAndNotInTable(): void
    {
        $spy = new BinderSpy(new SymbolTable());

        $useToken = new Token('T_KEYWORD', 'use', 1, 1);
        $depToken = new Token('T_IDENTIFIER', 'App.Services.OrderService', 1, 5);
        $dep      = new DependencyStatementNode($depToken, 'App.Services.OrderService');
        $useNode  = new UseNode($useToken, [$dep]);

        $spy->bind($this->makeProgram([$useNode]));

        // 'UserService' is not the last segment and not in the table.
        $result = $spy->publicCategorizeType('UserService');
        $this->assertSame('unknown', $result['category']);
    }

    // -------------------------------------------------------------------------
    // resolvePropertyTypes
    // -------------------------------------------------------------------------

    public function testResolvePropertyTypesSinglePrimitive(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $prop = new PropertyNode($this->makeToken(), ['String'], 'name');
        $spy->publicResolvePropertyTypes($prop);

        $this->assertCount(1, $prop->resolvedTypeInfo);
        $this->assertSame('primitive', $prop->resolvedTypeInfo[0]['category']);
        $this->assertSame('string', $prop->resolvedTypeInfo[0]['native']);
    }

    public function testResolvePropertyTypesMultipleTypes(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $prop = new PropertyNode($this->makeToken(), ['String', 'Int', 'Null'], 'value');
        $spy->publicResolvePropertyTypes($prop);

        $this->assertCount(3, $prop->resolvedTypeInfo);
        $this->assertSame('string', $prop->resolvedTypeInfo[0]['native']);
        $this->assertSame('int', $prop->resolvedTypeInfo[1]['native']);
        $this->assertSame('null', $prop->resolvedTypeInfo[2]['native']);
    }

    public function testResolvePropertyTypesSupertype(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $prop = new PropertyNode($this->makeToken(), ['Email'], 'email');
        $spy->publicResolvePropertyTypes($prop);

        $this->assertCount(1, $prop->resolvedTypeInfo);
        $this->assertSame('supertype', $prop->resolvedTypeInfo[0]['category']);
        $this->assertStringContainsString('Email', $prop->resolvedTypeInfo[0]['class']);
    }

    public function testResolvePropertyTypesMetatype(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $prop = new PropertyNode($this->makeToken(), ['Date'], 'createdAt');
        $spy->publicResolvePropertyTypes($prop);

        $this->assertCount(1, $prop->resolvedTypeInfo);
        $this->assertSame('metatype', $prop->resolvedTypeInfo[0]['category']);
    }

    public function testResolvePropertyTypesEmptyTypesArray(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $prop = new PropertyNode($this->makeToken(), [], 'anything');
        $spy->publicResolvePropertyTypes($prop);

        $this->assertSame([], $prop->resolvedTypeInfo);
    }

    public function testResolvePropertyTypesForParamArgumentNode(): void
    {
        $spy = $this->makeSpyWithEmptyProgram();

        $param = new ParamArgumentNode($this->makeToken(), ['Bool'], 'flag');
        $spy->publicResolvePropertyTypes($param);

        $this->assertCount(1, $param->resolvedTypeInfo);
        $this->assertSame('primitive', $param->resolvedTypeInfo[0]['category']);
        $this->assertSame('bool', $param->resolvedTypeInfo[0]['native']);
    }

    // -------------------------------------------------------------------------
    // Full bind() path — property types resolved on class body members
    // -------------------------------------------------------------------------

    public function testBindResolvesPropertyTypesForClassBodyMembers(): void
    {
        $table     = new SymbolTable();
        $binder    = new Binder($table);
        $classNode = $this->makeClassNode('Person');

        $propToken = new Token('T_IDENTIFIER', 'name', 1, 1);
        $prop      = new PropertyNode($propToken, ['String'], 'name');
        $classNode->body->children = [$prop];

        $binder->bind($this->makeProgram([$classNode]));

        $this->assertNotEmpty($prop->resolvedTypeInfo);
        $this->assertSame('primitive', $prop->resolvedTypeInfo[0]['category']);
        $this->assertSame('string', $prop->resolvedTypeInfo[0]['native']);
    }

    public function testBindResolvesSupertypePropertyInClassBody(): void
    {
        $table     = new SymbolTable();
        $binder    = new Binder($table);
        $classNode = $this->makeClassNode('Contact');

        $propToken = new Token('T_IDENTIFIER', 'email', 1, 1);
        $prop      = new PropertyNode($propToken, ['Email'], 'email');
        $classNode->body->children = [$prop];

        $binder->bind($this->makeProgram([$classNode]));

        $this->assertSame('supertype', $prop->resolvedTypeInfo[0]['category']);
    }

    // -------------------------------------------------------------------------
    // Public API — binders field is replaceable
    // -------------------------------------------------------------------------

    public function testBindersFieldIsPublicAndReplaceable(): void
    {
        $binder = new Binder(new SymbolTable());
        $binder->binders = [];
        $program = $this->makeProgram();
        $binder->bind($program);
        $this->assertSame([], $binder->binders);
    }

    public function testReplacingBindersSkipsSubBinderProcessing(): void
    {
        $table     = new SymbolTable();
        $binder    = new Binder($table);
        $binder->binders = []; // skip all sub-binders

        $classNode = $this->makeClassNode('SomeClass');
        $propToken = new Token('T_IDENTIFIER', 'val', 1, 1);
        $prop      = new PropertyNode($propToken, ['Int'], 'val');
        $classNode->body->children = [$prop];

        $binder->bind($this->makeProgram([$classNode]));

        // With all binders replaced, type registration and resolution are also skipped —
        // both now live in dedicated binder classes (TypeRegistrationBinder /
        // PropertyTypeResolutionBinder), so an empty binders array means nothing runs.
        $this->assertNull($table->getTypeDefinition('SomeClass'));
        $this->assertEmpty($prop->resolvedTypeInfo ?? []);
    }
}
