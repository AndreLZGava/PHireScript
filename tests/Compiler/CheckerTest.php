<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Checker;
use PHireScript\Compiler\Checker\Declaration\Class\MethodReturnChecker;
use PHireScript\Compiler\Checker\Declaration\ClassBodyChecker;
use PHireScript\Compiler\Program;
use PHireScript\SymbolTable;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\ClassBodyNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\DependencyInjectionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\MethodDeclarationNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Runtime\Exceptions\CheckerException;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Tests\Compiler\Helpers\StringableReturnType;

class CheckerTest extends TestCase
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

    private function makeClassNode(
        string $type = 'class',
        array $modifiers = [],
        bool $withDI = false,
    ): ClassNode {
        $token = new Token('T_KEYWORD', $type, 1, 1);
        $classNode = new ClassNode($token);
        $classNode->name = 'TestClass';
        $classNode->modifiers = $modifiers;

        $bodyToken = new Token('T_SYMBOL', '{', 1, 5);
        $classNode->body = new ClassBodyNode($bodyToken, 'TestClass', $type);

        if ($withDI) {
            $diToken = new Token('T_IDENTIFIER', 'scoped', 1, 10);
            $classNode->typeDependencyInjection = new DependencyInjectionNode($diToken);
        }

        return $classNode;
    }

    private function callCheckClassBody(Checker $checker, ClassNode $classNode): void
    {
        (new ClassBodyChecker())->check($classNode, $checker);
    }

    private function callEnsureReturnsForMethods(Checker $checker, MethodDeclarationNode $method): void
    {
        (new MethodReturnChecker())->check($method, $checker);
    }

    private function makeMethod(
        string $name,
        string $returnType,
        bool $mustBeBool = false,
        bool $mustBeVoid = false,
    ): MethodDeclarationNode {
        $token = new Token('T_IDENTIFIER', $name, 1, 1);
        $method = new MethodDeclarationNode($token, $name, mustBeBool: $mustBeBool, mustBeVoid: $mustBeVoid);
        // Node::__construct is not called by MethodDeclarationNode — set manually.
        $method->line   = 1;
        $method->column = 1;
        $method->returnType = new StringableReturnType($returnType);
        return $method;
    }

    // -------------------------------------------------------------------------
    // check() — empty program
    // -------------------------------------------------------------------------

    public function testCheckEmptyProgramDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        (new Checker())->check($this->makeProgram(), new SymbolTable());
    }

    public function testCheckProgramWithNoClassNodesDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        // UseNode or other non-class statements should be silently ignored.
        $program = $this->makeProgram();
        $program->statements = [new \stdClass()]; // non-ClassNode statement
        // stdClass won't match ClassNode, so check() does nothing harmful.
        // We need a Program, not stmts that trigger instanceof ClassNode.
        $program->statements = [];
        (new Checker())->check($program, new SymbolTable());
    }

    // -------------------------------------------------------------------------
    // ClassChecker integration — lifecycle validation (via check())
    // -------------------------------------------------------------------------

    public function testClassWithoutLifecycleThrowsCompileException(): void
    {
        $this->expectException(CompileException::class);
        $checker = new Checker();
        $classNode = $this->makeClassNode('class'); // no modifiers, no DI
        $checker->check($this->makeProgram([$classNode]), new SymbolTable());
    }

    public function testAbstractClassPassesLifecycleCheck(): void
    {
        $this->expectNotToPerformAssertions();
        $checker = new Checker();
        $classNode = $this->makeClassNode('class', ['abstract']);
        $checker->check($this->makeProgram([$classNode]), new SymbolTable());
    }

    public function testTraitPassesLifecycleCheck(): void
    {
        $this->expectNotToPerformAssertions();
        $checker = new Checker();
        $classNode = $this->makeClassNode('trait');
        $checker->check($this->makeProgram([$classNode]), new SymbolTable());
    }

    public function testClassWithDependencyInjectionPassesLifecycleCheck(): void
    {
        $this->expectNotToPerformAssertions();
        $checker = new Checker();
        $classNode = $this->makeClassNode('class', [], true); // withDI = true
        $checker->check($this->makeProgram([$classNode]), new SymbolTable());
    }

    // -------------------------------------------------------------------------
    // checkClassBody — readonly + defaultValue
    // -------------------------------------------------------------------------

    public function testReadonlyClassWithDefaultValueThrows(): void
    {
        $this->expectException(\Exception::class);

        $valueNode = new Program($this->makeToken('value'));
        $prop = new PropertyNode($this->makeToken(), ['String'], 'age', $valueNode);

        $classNode = $this->makeClassNode();
        $classNode->readOnly = true;
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    public function testReadonlyClassWithNullDefaultValueDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $prop = new PropertyNode($this->makeToken(), ['String'], 'age'); // value defaults to null

        $classNode = $this->makeClassNode();
        $classNode->readOnly = true;
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    public function testNonReadonlyClassWithDefaultValueDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $valueNode = new Program($this->makeToken('value'));
        $prop = new PropertyNode($this->makeToken(), ['String'], 'name', $valueNode);

        $classNode = $this->makeClassNode();
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    // -------------------------------------------------------------------------
    // checkClassBody — abstract property constraint
    // -------------------------------------------------------------------------

    public function testAbstractPropertyInNonAbstractClassThrows(): void
    {
        $this->expectException(\Exception::class);

        $prop = new PropertyNode($this->makeToken(), ['String'], 'value', null, ['abstract']);

        $classNode = $this->makeClassNode(); // non-abstract class
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    public function testAbstractPropertyInAbstractClassDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $prop = new PropertyNode($this->makeToken(), ['String'], 'value', null, ['abstract']);

        $classNode = $this->makeClassNode('class', ['abstract']);
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    public function testNonAbstractPropertyInNonAbstractClassDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $prop = new PropertyNode($this->makeToken(), ['String'], 'value');

        $classNode = $this->makeClassNode();
        $classNode->body->children = [$prop];

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    public function testEmptyBodyDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();

        $classNode = $this->makeClassNode();
        // body->children already empty from makeClassNode()

        $this->callCheckClassBody(new Checker(), $classNode);
    }

    // -------------------------------------------------------------------------
    // ensureReturnsForMethods — mustBeBool
    // -------------------------------------------------------------------------

    public function testMustBeBoolWithExclusiveBoolReturnDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('isValid', 'Bool', mustBeBool: true)
        );
    }

    public function testMustBeBoolWithNonBoolReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('isValid', 'String', mustBeBool: true)
        );
    }

    public function testMustBeBoolWithUnionReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        // Union types are forbidden even when Bool is one of the types.
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('isValid', 'Bool|String', mustBeBool: true)
        );
    }

    public function testMustBeBoolWithVoidReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('isValid', 'Void', mustBeBool: true)
        );
    }

    // -------------------------------------------------------------------------
    // ensureReturnsForMethods — mustBeVoid
    // -------------------------------------------------------------------------

    public function testMustBeVoidWithExclusiveVoidReturnDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('doAction', 'Void', mustBeVoid: true)
        );
    }

    public function testMustBeVoidWithNonVoidReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('doAction', 'Int', mustBeVoid: true)
        );
    }

    public function testMustBeVoidWithUnionReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('doAction', 'Void|Int', mustBeVoid: true)
        );
    }

    public function testMustBeVoidWithBoolReturnThrowsCheckerException(): void
    {
        $this->expectException(CheckerException::class);
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('doAction', 'Bool', mustBeVoid: true)
        );
    }

    // -------------------------------------------------------------------------
    // ensureReturnsForMethods — no constraints
    // -------------------------------------------------------------------------

    public function testNoConstraintsAnyReturnTypeDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('getData', 'String|Int|Bool')
        );
    }

    public function testNoConstraintsWithBoolReturnDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('getData', 'Bool') // no mustBeBool flag
        );
    }

    public function testNoConstraintsWithVoidReturnDoesNotThrow(): void
    {
        $this->expectNotToPerformAssertions();
        $this->callEnsureReturnsForMethods(
            new Checker(),
            $this->makeMethod('doThing', 'Void') // no mustBeVoid flag
        );
    }

    // -------------------------------------------------------------------------
    // Public API — checkers field is replaceable
    // -------------------------------------------------------------------------

    public function testCheckersFieldIsPublicAndReplaceable(): void
    {
        $checker = new Checker();
        $checker->checkers = [];
        $checker->check($this->makeProgram(), new SymbolTable());
        $this->assertSame([], $checker->checkers);
    }

    public function testReplacingCheckersSkipsSubCheckerValidation(): void
    {
        $this->expectNotToPerformAssertions();
        $checker           = new Checker();
        $checker->checkers = []; // no ClassChecker → lifecycle not enforced
        $classNode = $this->makeClassNode('class'); // would normally fail lifecycle check
        $checker->check($this->makeProgram([$classNode]), new SymbolTable());
    }
}
