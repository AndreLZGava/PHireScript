<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Processors;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Processors\VariablesHandler;

class VariablesHandlerTest extends TestCase
{
    private VariablesHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new VariablesHandler();
    }

    public function testPrefixesPlainIdentifier(): void
    {
        $this->assertSame('($foo)', $this->handler->process('(foo)'));
    }

    public function testDoesNotPrefixAlreadyPrefixed(): void
    {
        $this->assertSame('($foo)', $this->handler->process('($foo)'));
    }

    public function testDoesNotPrefixStringArgument(): void
    {
        $this->assertSame('("hello")', $this->handler->process('("hello")'));
    }

    public function testDoesNotPrefixNumericArgument(): void
    {
        $this->assertSame('(42)', $this->handler->process('(42)'));
    }

    public function testHandlesMultipleParams(): void
    {
        // implode uses ', ' so spacing is normalised
        $this->assertSame('($foo, $bar)', $this->handler->process('(foo, bar)'));
    }

    public function testDoesNotPrefixAlreadyPrefixedMixed(): void
    {
        $this->assertSame('($foo, $bar)', $this->handler->process('($foo, bar)'));
    }

    public function testEmptyParens(): void
    {
        $this->assertSame('()', $this->handler->process('()'));
    }

    public function testNoParens(): void
    {
        $this->assertSame('foo bar', $this->handler->process('foo bar'));
    }
}
