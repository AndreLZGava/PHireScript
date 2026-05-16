<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Processors;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Processors\NativeTypesHandler;

class NativeTypesHandlerTest extends TestCase
{
    private NativeTypesHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new NativeTypesHandler();
    }

    public function testReplacesIntReturnType(): void
    {
        $this->assertSame(': int', $this->handler->process(': Int'));
    }

    public function testReplacesStringReturnType(): void
    {
        $this->assertSame(': string', $this->handler->process(': String'));
    }

    public function testReplacesBoolReturnType(): void
    {
        $this->assertSame(': bool', $this->handler->process(': Bool'));
    }

    public function testReplacesFloatReturnType(): void
    {
        $this->assertSame(': float', $this->handler->process(': Float'));
    }

    public function testReplacesArrayReturnType(): void
    {
        $this->assertSame(': array', $this->handler->process(': Array'));
    }

    public function testReplacesObjectReturnType(): void
    {
        $this->assertSame(': object', $this->handler->process(': Object'));
    }

    public function testReplacesVoidReturnType(): void
    {
        $this->assertSame(': void', $this->handler->process(': Void'));
    }

    public function testReplacesCastSyntaxInt(): void
    {
        $this->assertSame('(int)($value)', $this->handler->process('Int($value)'));
    }

    public function testReplacesCastSyntaxString(): void
    {
        $this->assertSame('(string)($value)', $this->handler->process('String($value)'));
    }

    public function testReplacesCastSyntaxBool(): void
    {
        $this->assertSame('(bool)($flag)', $this->handler->process('Bool($flag)'));
    }

    public function testDoesNotReplaceInStrings(): void
    {
        // A function declaration: return type annotation gets converted
        $input    = 'function foo(): Int';
        $expected = 'function foo(): int';

        $this->assertSame($expected, $this->handler->process($input));
    }

    public function testMultipleReplacementsInOneLine(): void
    {
        $input    = 'function foo(): Int { $x = String($y); }';
        $expected = 'function foo(): int { $x = (string)($y); }';

        $this->assertSame($expected, $this->handler->process($input));
    }
}
