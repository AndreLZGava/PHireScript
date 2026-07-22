<?php

declare(strict_types=1);

namespace PHireScript\Tests;

use PHPUnit\Framework\TestCase;
use PHireScript\Transpiler;
use PHireScript\DependencyGraphBuilder;
use PHireScript\Core\CompilerContext;
use PHireScript\Core\CompileMode;
use PHireScript\Runtime\Exceptions\CompileException;

class TranspilerTest extends TestCase
{
    private function makeTranspiler(): Transpiler
    {
        return new Transpiler(
            config: [
                'dev' => false,
                'namespace' => 'App',
                'resolver' => 'laravel',
                'paths' => ['source' => 'samples', 'dist' => 'dist'],
            ],
            dependencyManager: new DependencyGraphBuilder(),
            context: new CompilerContext(CompileMode::BUILD),
        );
    }

    private function simpleInterface(): string
    {
        return <<<'PS'
            pkg PHireScript.Test

            interface Greeter {
                greet!(): String
            }
            PS;
    }

    private function simpleInterfacePath(): string
    {
        return 'samples/test/Greeter.phs';
    }

    // -------------------------------------------------------------------------
    // getCodeBeforeGenerator()
    // -------------------------------------------------------------------------

    public function testGetCodeBeforeGeneratorIsEmptyBeforeAnyCompileCall(): void
    {
        $this->assertSame('', $this->makeTranspiler()->getCodeBeforeGenerator());
    }

    public function testGetCodeBeforeGeneratorIsPopulatedAfterSuccessfulCompile(): void
    {
        $transpiler = $this->makeTranspiler();
        $transpiler->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertNotEmpty($transpiler->getCodeBeforeGenerator());
    }

    public function testGetCodeBeforeGeneratorContainsPhpOpenTag(): void
    {
        $transpiler = $this->makeTranspiler();
        $transpiler->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertStringContainsString('<?php', $transpiler->getCodeBeforeGenerator());
    }

    public function testGetCodeBeforeGeneratorResetsToEmptyWhenCompileThrowsBeforeEmission(): void
    {
        $transpiler = $this->makeTranspiler();
        $transpiler->compile($this->simpleInterface(), $this->simpleInterfacePath());
        $this->assertNotEmpty($transpiler->getCodeBeforeGenerator());

        try {
            $transpiler->compile('namespace Foo', 'test.phs');
        } catch (CompileException) {
        }

        $this->assertSame('', $transpiler->getCodeBeforeGenerator());
    }

    // -------------------------------------------------------------------------
    // compile() — happy paths
    // -------------------------------------------------------------------------

    public function testCompileReturnsStringWithPhpOpenTag(): void
    {
        $result = $this->makeTranspiler()->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertStringContainsString('<?php', $result);
    }

    public function testCompileReturnsNonEmptyString(): void
    {
        $result = $this->makeTranspiler()->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertNotEmpty($result);
    }

    public function testCompileOutputContainsInterfaceName(): void
    {
        $result = $this->makeTranspiler()->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertStringContainsString('Greeter', $result);
    }

    public function testCompileOutputContainsInterfaceKeyword(): void
    {
        $result = $this->makeTranspiler()->compile($this->simpleInterface(), $this->simpleInterfacePath());

        $this->assertStringContainsString('interface', $result);
    }

    public function testCompileSimpleClass(): void
    {
        $code = <<<'PS'
            pkg PHireScript.Test

            class Box as scoped {}
            PS;

        $result = $this->makeTranspiler()->compile($code, 'samples/test/Box.phs');

        $this->assertStringContainsString('class Box', $result);
    }

    public function testCompileTypeDeclaration(): void
    {
        $code = <<<'PS'
            pkg PHireScript.Test

            type Payload as scoped {
                String name
            }
            PS;

        $result = $this->makeTranspiler()->compile($code, 'samples/test/Payload.phs');

        $this->assertStringContainsString('Payload', $result);
    }

    // -------------------------------------------------------------------------
    // compile() — Validator errors (CompileException propagates)
    // -------------------------------------------------------------------------

    public function testCompileThrowsForForbiddenNamespaceKeyword(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('namespace Foo', 'test.phs');
    }

    public function testCompileThrowsForSemicolon(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('pkg PHireScript.Test;', 'test.phs');
    }

    public function testCompileThrowsForLowercaseVoid(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('void', 'test.phs');
    }

    public function testCompileThrowsForFunctionKeyword(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('function foo() {}', 'test.phs');
    }

    public function testCompileThrowsForPrivateKeyword(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('private String name', 'test.phs');
    }

    public function testCompileThrowsForUnbalancedOpenBrace(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile(<<<'PS'
            pkg PHireScript.Test

            interface Unclosed {
            PS, 'test.phs');
    }

    public function testCompileThrowsWhenObjectDefinedWithoutPkg(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile('class Orphan as scoped {}', 'test.phs');
    }

    public function testCompileThrowsForMultipleObjectsInOneFile(): void
    {
        $this->expectException(CompileException::class);

        $this->makeTranspiler()->compile(<<<'PS'
            pkg PHireScript.Test

            interface One {}
            interface Two {}
            PS, 'test.phs');
    }
}
