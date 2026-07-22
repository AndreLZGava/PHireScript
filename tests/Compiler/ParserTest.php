<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Parser;
use PHireScript\Compiler\Program;
use PHireScript\Compiler\Scanner;
use PHireScript\Core\CompileMode;
use PHireScript\Core\CompilerContext;
use PHireScript\DependencyGraphBuilder;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\InterfaceNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\PackageNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\TraitNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\UseNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\InterfaceMethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;

class ParserTest extends TestCase
{
    private function parse(string $code, string $path = 'Test.phs'): Program
    {
        $tokens = (new Scanner($code, $path))->tokenize();
        $parser = new Parser(
            config: ['namespace' => 'App', 'paths' => ['source' => 'samples']],
            dependencyBuilder: new DependencyGraphBuilder(),
            context: new CompilerContext(mode: CompileMode::DEBUG, inMemory: true),
        );
        return $parser->parse($tokens, $path);
    }

    private function firstStatement(string $code, string $path = 'Test.phs'): mixed
    {
        $program = $this->parse($code, $path);
        $this->assertNotEmpty($program->statements, 'Expected at least one statement from: ' . $code);
        return $program->statements[0];
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T|null
     */
    private function findInBody(array $children, string $class): mixed
    {
        foreach ($children as $child) {
            if ($child instanceof $class) {
                return $child;
            }
        }
        return null;
    }

    // -------------------------------------------------------------------------
    // Return type and program structure
    // -------------------------------------------------------------------------

    public function testParseAlwaysReturnsProgram(): void
    {
        $result = $this->parse('');
        $this->assertInstanceOf(Program::class, $result);
    }

    public function testEmptyInputProducesNoStatements(): void
    {
        $program = $this->parse('');
        $this->assertEmpty($program->statements);
    }

    public function testMultipleDeclarationsProduceMultipleStatements(): void
    {
        $program = $this->parse("pkg PHireScript.Foo.IFoo\ninterface IFoo {}", 'IFoo.phs');
        $this->assertCount(2, $program->statements);
    }

    // -------------------------------------------------------------------------
    // Package declaration
    // -------------------------------------------------------------------------

    public function testParsesPackageDeclaration(): void
    {
        $node = $this->firstStatement('pkg PHireScript.Foo');
        $this->assertInstanceOf(PackageNode::class, $node);
    }

    public function testPackageObjectIsLastSegment(): void
    {
        /** @var PackageNode $node */
        $node = $this->firstStatement("pkg PHireScript.Samples1.Foo\nclass Foo {}", 'Foo.phs');
        $this->assertSame('Foo', $node->object);
    }

    public function testPackagePropertyContainsFullPath(): void
    {
        /** @var PackageNode $node */
        $node = $this->firstStatement("pkg PHireScript.Samples1.Foo\nclass Foo {}", 'Foo.phs');
        $this->assertSame('PHireScript.Samples1.Foo', $node->package);
    }

    // -------------------------------------------------------------------------
    // Use statement
    // -------------------------------------------------------------------------

    public function testParsesUseStatement(): void
    {
        $node = $this->firstStatement('use PHireScript.Foo.Bar');
        $this->assertInstanceOf(UseNode::class, $node);
    }

    public function testUseStatementHasNonEmptyPackages(): void
    {
        /** @var UseNode $node */
        $node = $this->firstStatement("use PHireScript.Foo.Bar\n");
        $this->assertNotEmpty($node->packages);
    }

    // -------------------------------------------------------------------------
    // Comment
    // -------------------------------------------------------------------------

    public function testSingleLineCommentDoesNotProduceTopLevelStatement(): void
    {
        $program = $this->parse("// just a comment\ninterface IFoo {}");
        $nonComment = array_values(array_filter(
            $program->statements,
            fn ($s) => $s instanceof InterfaceNode
        ));
        $this->assertCount(1, $nonComment);
    }

    // -------------------------------------------------------------------------
    // Class
    // -------------------------------------------------------------------------

    public function testParsesEmptyClass(): void
    {
        $node = $this->firstStatement('class Foo {}');
        $this->assertInstanceOf(ClassNode::class, $node);
    }

    public function testClassNameIsPopulated(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo {}');
        $this->assertSame('Foo', $node->name);
    }

    public function testClassTypePropertyReflectsKeyword(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo {}');
        $this->assertSame('class', $node->type);
    }

    public function testAbstractClassHasAbstractModifier(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('abstract class Foo {}');
        $this->assertInstanceOf(ClassNode::class, $node);
        $this->assertContains('abstract', $node->modifiers);
    }

    public function testImmutableProducesClassNodeWithImmutableType(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('immutable Foo {}');
        $this->assertInstanceOf(ClassNode::class, $node);
        $this->assertSame('immutable', $node->type);
        $this->assertSame('Foo', $node->name);
    }

    public function testTypeKeywordProducesClassNodeWithTypeFlag(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('type Foo {}');
        $this->assertInstanceOf(ClassNode::class, $node);
        $this->assertSame('type', $node->type);
        $this->assertSame('Foo', $node->name);
    }

    // -------------------------------------------------------------------------
    // Interface
    // -------------------------------------------------------------------------

    public function testParsesEmptyInterface(): void
    {
        $node = $this->firstStatement('interface IFoo {}');
        $this->assertInstanceOf(InterfaceNode::class, $node);
    }

    public function testInterfaceNameIsPopulated(): void
    {
        /** @var InterfaceNode $node */
        $node = $this->firstStatement('interface IFoo {}');
        $this->assertSame('IFoo', $node->name);
    }

    public function testInterfaceWithExtendsHasExtendsNode(): void
    {
        /** @var InterfaceNode $node */
        $node = $this->firstStatement('interface IFoo extends IBar {}');
        $this->assertNotNull($node->extends);
    }

    // -------------------------------------------------------------------------
    // Trait
    // -------------------------------------------------------------------------

    public function testParsesEmptyTrait(): void
    {
        $node = $this->firstStatement('trait MyTrait {}');
        $this->assertInstanceOf(TraitNode::class, $node);
    }

    public function testTraitNameIsPopulated(): void
    {
        /** @var TraitNode $node */
        $node = $this->firstStatement('trait MyTrait {}');
        $this->assertSame('MyTrait', $node->name);
    }

    // -------------------------------------------------------------------------
    // Class — inheritance and composition
    // -------------------------------------------------------------------------

    public function testClassWithExtendsHasExtendsNode(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo extends Bar {}');
        $this->assertNotNull($node->extends);
    }

    public function testClassWithImplementsHasImplementsNode(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo implements IBar {}');
        $this->assertNotNull($node->implements);
    }

    public function testClassWithTraitHasWithNode(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo with MyTrait {}');
        $this->assertNotNull($node->with);
    }

    // -------------------------------------------------------------------------
    // Class body — properties (PHireScript syntax: Type name)
    // -------------------------------------------------------------------------

    public function testClassBodyHasProperty(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  String name\n}");
        $this->assertNotNull($node->body);
        $property = $this->findInBody($node->body->children, PropertyNode::class);
        $this->assertNotNull($property);
    }

    public function testPropertyNameIsPopulated(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  String name\n}");
        $property = $this->findInBody($node->body->children, PropertyNode::class);
        $this->assertSame('name', $property->name);
    }

    public function testPropertyTypesContainsDeclaredType(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  String name\n}");
        $property = $this->findInBody($node->body->children, PropertyNode::class);
        $this->assertContains('String', $property->types);
    }

    public function testPropertyWithUnionTypesHasBothTypes(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  String|Null name\n}");
        $property = $this->findInBody($node->body->children, PropertyNode::class);
        $this->assertCount(2, $property->types);
        $this->assertContains('String', $property->types);
        $this->assertContains('Null', $property->types);
    }

    public function testAbstractPropertyHasAbstractModifier(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("abstract class Foo {\n  abstract String name\n}");
        $property = $this->findInBody($node->body->children, PropertyNode::class);
        $this->assertContains('abstract', $property->modifiers);
    }

    public function testMultiplePropertiesAreAllPresent(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  String name\n  Int age\n}");
        $properties = array_values(array_filter(
            $node->body->children,
            fn ($c) => $c instanceof PropertyNode
        ));
        $this->assertCount(2, $properties);
    }

    // -------------------------------------------------------------------------
    // Class body — methods
    // -------------------------------------------------------------------------

    public function testClassBodyHasMethod(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  getName(): String {}\n}");
        $method = $this->findInBody($node->body->children, MethodDeclarationNode::class);
        $this->assertNotNull($method);
    }

    public function testMethodNameIsPopulated(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement("class Foo {\n  getName(): String {}\n}");
        $method = $this->findInBody($node->body->children, MethodDeclarationNode::class);
        $this->assertSame('getName', $method->name);
    }

    // -------------------------------------------------------------------------
    // Interface body — method signatures
    // -------------------------------------------------------------------------

    public function testInterfaceBodyHasMethodSignature(): void
    {
        /** @var InterfaceNode $node */
        $node = $this->firstStatement("interface IFoo {\n  getName(): String\n}");
        $this->assertNotNull($node->body);
        $this->assertNotEmpty($node->body->children);
    }

    public function testBoolMarkerOnInterfaceMethodSetsMustBeBool(): void
    {
        /** @var InterfaceNode $node */
        $node = $this->firstStatement("interface IFoo {\n  isActive?(): Bool\n}");
        $method = $this->findInBody($node->body->children, InterfaceMethodDeclarationNode::class);
        $this->assertNotNull($method);
        $this->assertTrue($method->mustBeBool);
    }

    public function testVoidMarkerOnInterfaceMethodSetsMustBeVoid(): void
    {
        /** @var InterfaceNode $node */
        $node = $this->firstStatement("interface IFoo {\n  doSomething!(): Void\n}");
        $method = $this->findInBody($node->body->children, InterfaceMethodDeclarationNode::class);
        $this->assertNotNull($method);
        $this->assertTrue($method->mustBeVoid);
    }

    // -------------------------------------------------------------------------
    // Token position tracking
    // -------------------------------------------------------------------------

    public function testFirstStatementTokenIsOnLine1(): void
    {
        /** @var ClassNode $node */
        $node = $this->firstStatement('class Foo {}');
        $this->assertSame(1, $node->token->line);
    }

    public function testSecondStatementTokenIsOnCorrectLine(): void
    {
        $program = $this->parse("pkg PHireScript.Foo.IFoo\ninterface IFoo {}", 'IFoo.phs');
        $this->assertSame(2, $program->statements[1]->token->line);
    }
}
