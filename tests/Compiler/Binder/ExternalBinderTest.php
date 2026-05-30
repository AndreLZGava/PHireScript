<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Binder;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Binder;
use PHireScript\Compiler\Binder\Declaration\ExternalBinder;
use PHireScript\Compiler\External\ExternalClassDescriptor;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExternalNode;
use PHireScript\Compiler\Parser\Ast\Nodes\Statements\NamespaceNode;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\SymbolTable;

class ExternalBinderTest extends TestCase
{
    private function makeToken(string $value = 'external'): Token
    {
        return new Token('T_KEYWORD', $value, 1, 1);
    }

    private function makeExternalNode(string $fqcn, ?string $alias = null): ExternalNode
    {
        $token = $this->makeToken();
        $node  = new ExternalNode($token);

        $nsToken            = new Token('T_IDENTIFIER', $fqcn, 1, 10);
        $ns                 = new NamespaceNode($nsToken, $fqcn, $alias);
        $node->namespaces   = [$ns];

        return $node;
    }

    private function makeBinder(?SymbolTable $table = null): Binder
    {
        return new Binder($table ?? new SymbolTable());
    }

    // -------------------------------------------------------------------------
    // mustBind
    // -------------------------------------------------------------------------

    public function testMustBindReturnsTrueForExternalNode(): void
    {
        $b = new ExternalBinder();
        $this->assertTrue($b->mustBind($this->makeExternalNode('DateTime')));
    }

    public function testMustBindReturnsFalseForOtherNode(): void
    {
        $b     = new ExternalBinder();
        $token = $this->makeToken('class');
        $other = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExternalNode($token);
        // Use a ClassNode to confirm false
        $class = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode($token);
        $this->assertFalse($b->mustBind($class));
    }

    // -------------------------------------------------------------------------
    // bind — happy path
    // -------------------------------------------------------------------------

    public function testBindRegistersDescriptorForDateTimeWithAlias(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        $binder->bind(
            new \PHireScript\Compiler\Program($this->makeToken('program'))
        );

        // Bind directly
        $eb = new ExternalBinder();
        $eb->bind($node, $binder);

        $descriptor = $table->getExternal('DateTimePhp');
        $this->assertInstanceOf(ExternalClassDescriptor::class, $descriptor);
        $this->assertSame('DateTime', $descriptor->className);
        $this->assertSame('DateTimePhp', $descriptor->alias);
    }

    public function testBindDescriptorContainsStaticMethod(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        (new ExternalBinder())->bind($node, $binder);

        $descriptor = $table->getExternal('DateTimePhp');
        $this->assertNotNull($descriptor);
        $this->assertTrue($descriptor->hasMethod('createFromFormat'));
        $this->assertTrue($descriptor->getMethod('createFromFormat')->isStatic);
    }

    public function testBindDescriptorContainsInstanceMethod(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        (new ExternalBinder())->bind($node, $binder);

        $descriptor = $table->getExternal('DateTimePhp');
        $this->assertNotNull($descriptor);
        $this->assertTrue($descriptor->hasMethod('format'));
        $this->assertFalse($descriptor->getMethod('format')->isStatic);
    }

    public function testBindDescriptorContainsConstant(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        (new ExternalBinder())->bind($node, $binder);

        $descriptor = $table->getExternal('DateTimePhp');
        $this->assertNotNull($descriptor);
        $this->assertTrue($descriptor->hasConstant('ATOM'));
    }

    public function testBindDescriptorContainsConstructorInfo(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        (new ExternalBinder())->bind($node, $binder);

        $descriptor = $table->getExternal('DateTimePhp');
        $this->assertNotNull($descriptor);
        $this->assertNotNull($descriptor->constructor);
        $this->assertTrue($descriptor->constructor->isPublic);
    }

    public function testIsExternalClassReturnsTrueAfterBind(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'DateTimePhp');

        (new ExternalBinder())->bind($node, $binder);

        $this->assertTrue($table->isExternalClass('DateTimePhp'));
        $this->assertFalse($table->isExternalClass('DateTime'));
    }

    // -------------------------------------------------------------------------
    // bind — error: class not found (FR-008)
    // -------------------------------------------------------------------------

    public function testBindEmitsWarningWhenClassNotInAutoloader(): void
    {
        $table  = new SymbolTable();
        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('NonExistent\\ClassName\\ThatDoesNotExist', 'NonExistentAlias');

        // Should not throw — emits warning and registers empty descriptor
        (new ExternalBinder())->bind($node, $binder);

        $descriptor = $table->getExternal('NonExistentAlias');
        $this->assertNotNull($descriptor);
        $this->assertEmpty($descriptor->methods);
        $this->assertEmpty($descriptor->constants);
    }

    // -------------------------------------------------------------------------
    // bind — error: conflict with PHireScript native class (FR-014)
    // -------------------------------------------------------------------------

    public function testBindThrowsWhenAliasConflictsWithNativeClass(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessageMatches('/conflicts with a PHireScript native class/');

        $table = new SymbolTable();
        // Register a PHireScript native class with same name as the alias
        $token     = new Token('T_KEYWORD', 'class', 1, 1);
        $classNode = new \PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ClassNode($token);
        $classNode->name = 'MyDate';
        $table->registerTypeDefinition('MyDate', $classNode);

        $binder = $this->makeBinder($table);
        $node   = $this->makeExternalNode('DateTime', 'MyDate');

        (new ExternalBinder())->bind($node, $binder);
    }
}
