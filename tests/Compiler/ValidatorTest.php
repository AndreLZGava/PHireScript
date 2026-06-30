<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Scanner;
use PHireScript\Compiler\Validator;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Runtime\Exceptions\CompileException;

class ValidatorTest extends TestCase
{
    // -------------------------------------------------------------------------
    // Helpers
    // -------------------------------------------------------------------------

    /**
     * Tokenise a source string and run it through the Validator.
     * The Scanner path is given a .ps extension so test-only patterns are skipped.
     */
    private function validate(string $code): void
    {
        $tokens = (new Scanner($code, 'test.ps'))->tokenize();
        (new Validator())->validate($tokens);
    }

    /**
     * Build a single synthetic Token and validate it directly, without involving
     * the Scanner.  Useful when a forbidden word would never be produced by the
     * Scanner (e.g. lowercase keywords that the Scanner never emits).
     */
    private function validateSingleToken(string $type, string $value): void
    {
        $token = new Token($type, $value, 1, 1);
        (new Validator())->validate([$token]);
    }

    // -------------------------------------------------------------------------
    // Happy path
    // -------------------------------------------------------------------------

    public function testValidCodePassesValidation(): void
    {
        // Simple variable declaration — no forbidden tokens, balanced brackets.
        $this->expectNotToPerformAssertions();
        $this->validate('x: Int = 1');
    }

    // -------------------------------------------------------------------------
    // Forbidden tokens (using synthetic tokens so we stay independent of Scanner)
    // -------------------------------------------------------------------------

    public function testForbidsNamespace(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'namespace');
    }

    public function testForbidsSemicolon(): void
    {
        $this->expectException(CompileException::class);
        // ';' is produced by the Scanner as T_SYMBOL
        $this->validateSingleToken('T_SYMBOL', ';');
    }

    public function testForbidsDoubleColons(): void
    {
        $this->expectException(CompileException::class);
        // '::' is produced by the Scanner as T_MODIFIER
        $this->validateSingleToken('T_MODIFIER', '::');
    }

    public function testForbidsArrow(): void
    {
        $this->expectException(CompileException::class);
        // '->' is produced by the Scanner as T_MODIFIER
        $this->validateSingleToken('T_MODIFIER', '->');
    }

    public function testForbidsVoidLowercase(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'void');
    }

    public function testForbidsStringLowercase(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'string');
    }

    public function testForbidsFunctionKeyword(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'function');
    }

    public function testForbidsPublic(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'public');
    }

    public function testForbidsPrivate(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'private');
    }

    public function testForbidsProtected(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'protected');
    }

    public function testForbidsVarDump(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'var_dump');
    }

    public function testForbidsDie(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'die');
    }

    public function testForbidsEval(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'eval');
    }

    // -------------------------------------------------------------------------
    // Forbidden accessor tokens
    // -------------------------------------------------------------------------

    public function testForbidsAccessorReversePlusLeft(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '+<');
    }

    public function testForbidsAccessorCrossedAngle(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '><');
    }

    // -------------------------------------------------------------------------
    // Unbalanced brackets
    // -------------------------------------------------------------------------

    public function testUnbalancedOpenParen(): void
    {
        $this->expectException(CompileException::class);
        $this->validate('(');
    }

    public function testUnbalancedCloseParen(): void
    {
        $this->expectException(CompileException::class);
        $this->validate(')');
    }

    public function testUnbalancedCurlyBraces(): void
    {
        $this->expectException(CompileException::class);
        $this->validate('{');
    }

    public function testUnbalancedSquareBrackets(): void
    {
        $this->expectException(CompileException::class);
        $this->validate('[');
    }

    public function testBalancedBracketsPass(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validate('({}[])');
    }

    // -------------------------------------------------------------------------
    // pkg rules
    // -------------------------------------------------------------------------

    public function testMultiplePkgThrows(): void
    {
        $this->expectException(CompileException::class);
        // Provide two 'pkg' keyword tokens directly — Scanner would normally
        // only emit one per well-formed file, but the Validator must catch it
        // regardless.
        $tokens = [
            new Token('T_KEYWORD', 'pkg', 1, 1),
            new Token('T_IDENTIFIER', 'App.One', 1, 5),
            new Token('T_EOL', "\n", 1, 12),
            new Token('T_KEYWORD', 'pkg', 2, 1),
            new Token('T_IDENTIFIER', 'App.Two', 2, 5),
        ];
        (new Validator())->validate($tokens);
    }

    public function testClassWithoutPkgThrows(): void
    {
        $this->expectException(CompileException::class);
        // A class keyword triggers $mustHavePkg = true; without a preceding
        // 'pkg' token the Validator must throw.
        $this->validate('class Foo { }');
    }

    public function testPkgWithClassPasses(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validate("pkg App.Foo\nclass Foo { }");
    }

    // -------------------------------------------------------------------------
    // Multiple class/interface/trait declarations
    // -------------------------------------------------------------------------

    public function testMultipleClassDeclarationsThrows(): void
    {
        $this->expectException(CompileException::class);
        // Two 'class' keywords in the same token stream must be rejected.
        $tokens = [
            new Token('T_KEYWORD', 'pkg', 1, 1),
            new Token('T_IDENTIFIER', 'App.Foo', 1, 5),
            new Token('T_EOL', "\n", 1, 12),
            new Token('T_KEYWORD', 'class', 2, 1),
            new Token('T_IDENTIFIER', 'Foo', 2, 7),
            new Token('T_SYMBOL', '{', 2, 11),
            new Token('T_SYMBOL', '}', 2, 12),
            new Token('T_EOL', "\n", 2, 13),
            new Token('T_KEYWORD', 'class', 3, 1),
            new Token('T_IDENTIFIER', 'Bar', 3, 7),
            new Token('T_SYMBOL', '{', 3, 11),
            new Token('T_SYMBOL', '}', 3, 12),
        ];
        (new Validator())->validate($tokens);
    }

    public function testClassAndInterfaceInSameFileThrows(): void
    {
        $this->expectException(CompileException::class);
        $tokens = [
            new Token('T_KEYWORD', 'pkg', 1, 1),
            new Token('T_IDENTIFIER', 'App.Foo', 1, 5),
            new Token('T_EOL', "\n", 1, 12),
            new Token('T_KEYWORD', 'class', 2, 1),
            new Token('T_IDENTIFIER', 'Foo', 2, 7),
            new Token('T_SYMBOL', '{', 2, 11),
            new Token('T_SYMBOL', '}', 2, 12),
            new Token('T_EOL', "\n", 2, 13),
            new Token('T_KEYWORD', 'interface', 3, 1),
            new Token('T_IDENTIFIER', 'Bar', 3, 10),
        ];
        (new Validator())->validate($tokens);
    }

    // -------------------------------------------------------------------------
    // Remaining forbidden tokens
    // -------------------------------------------------------------------------

    public function testForbidsIntLowercase(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'int');
    }

    public function testForbidsFloatLowercase(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'float');
    }

    public function testForbidsBoolLowercase(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'bool');
    }

    public function testForbidsStdClass(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'stdClass');
    }

    public function testForbidsArray(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'array');
    }

    public function testForbidsArrayKeyExists(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', 'array_key_exists');
    }

    public function testForbidsConstructKeyword(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_IDENTIFIER', '__construct');
    }

    // -------------------------------------------------------------------------
    // Remaining forbidden accessor tokens
    // -------------------------------------------------------------------------

    public function testForbidsAccessorHashLeft(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '#<');
    }

    public function testForbidsAccessorAsteriskLeft(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '*<');
    }

    public function testForbidsAccessorOpenCloseAngle(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '<>');
    }

    public function testForbidsAccessorHashRight(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '#>');
    }

    public function testForbidsAccessorAsteriskRight(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '*>');
    }

    public function testForbidsAccessorPlusRight(): void
    {
        $this->expectException(CompileException::class);
        $this->validateSingleToken('T_ACCESSORS', '+>');
    }

    // -------------------------------------------------------------------------
    // Angle bracket balancing — '<' and '>' are NOT tracked by BracketBalanceRule
    // because they are valid standalone accessor tokens (getter/setter syntax).
    // -------------------------------------------------------------------------

    public function testAngleBracketsAreNotTrackedAsBrackets(): void
    {
        $this->expectNotToPerformAssertions();
        // '<' alone must NOT cause a CompileException — it is a valid getter accessor.
        $this->validateSingleToken('T_SYMBOL', '<');
    }

    // -------------------------------------------------------------------------
    // pkg rules — interface and trait
    // -------------------------------------------------------------------------

    public function testInterfaceWithoutPkgThrows(): void
    {
        $this->expectException(CompileException::class);
        $this->validate('interface Foo { }');
    }

    public function testTraitWithoutPkgThrows(): void
    {
        $this->expectException(CompileException::class);
        $this->validate('trait Foo { }');
    }

    public function testPkgWithInterfacePasses(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validate("pkg App.Foo\ninterface Foo { }");
    }

    public function testPkgWithTraitPasses(): void
    {
        $this->expectNotToPerformAssertions();
        $this->validate("pkg App.Foo\ntrait Foo { }");
    }

    // -------------------------------------------------------------------------
    // Error message content
    // -------------------------------------------------------------------------

    public function testForbiddenTokenExceptionMessageContainsForbiddenWord(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessageMatches('/namespace/');
        $this->validateSingleToken('T_IDENTIFIER', 'namespace');
    }

    public function testForbiddenTokenExceptionMessageContainsHint(): void
    {
        $this->expectException(CompileException::class);
        $this->expectExceptionMessageMatches('/pkg/');
        $this->validateSingleToken('T_IDENTIFIER', 'namespace');
    }
}
