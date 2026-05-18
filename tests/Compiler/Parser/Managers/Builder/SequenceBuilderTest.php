<?php

declare(strict_types=1);

namespace PHireScript\Tests\Compiler\Parser\Managers\Builder;

use PHPUnit\Framework\TestCase;
use PHireScript\Compiler\Parser\Managers\TokenManager;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class SequenceBuilderTest extends TestCase
{
    private function tok(string $type, string $value = ''): Token
    {
        return new Token($type, $value ?: $type, 1, 1);
    }

    private function manager(array $tokens, int $currentPosition = 0): TokenManager
    {
        return new TokenManager('test', $tokens, $currentPosition);
    }

    private function primitive(string $value = 'String'): Token
    {
        return $this->tok('T_PRIMITIVE', $value);
    }

    private function identifier(string $value = 'name'): Token
    {
        return $this->tok('T_IDENTIFIER', $value);
    }

    private function number(string $value = '1'): Token
    {
        return $this->tok('T_NUMBER', $value);
    }

    private function pipe(): Token
    {
        return $this->tok('T_SYMBOL', '|');
    }

    private function colon(): Token
    {
        return $this->tok('T_SYMBOL', ':');
    }

    private function openParen(): Token
    {
        return $this->tok('T_SYMBOL', '(');
    }

    private function closeParen(): Token
    {
        return $this->tok('T_SYMBOL', ')');
    }

    private function arrow(): Token
    {
        return $this->tok('T_SYMBOL', '=>');
    }

    private function eol(): Token
    {
        return $this->tok('T_EOL', "\n");
    }

    // -------------------------------------------------------------------------
    // once / then
    // -------------------------------------------------------------------------

    public function testOnceMatchesSingleToken(): void
    {
        $manager = $this->manager([$this->primitive()]);

        $this->assertTrue(
            $manager->sequence()->lookAhead()->once(fn($t) => $t->isPrimitive())->match()
        );
    }

    public function testOnceFailsWhenTokenDoesNotMatch(): void
    {
        $manager = $this->manager([$this->identifier()]);

        $this->assertFalse(
            $manager->sequence()->lookAhead()->once(fn($t) => $t->isPrimitive())->match()
        );
    }

    public function testThenIsAliasForOnce(): void
    {
        $manager = $this->manager([$this->primitive()]);

        $this->assertTrue(
            $manager->sequence()->lookAhead()->then(fn($t) => $t->isPrimitive())->match()
        );
    }

    public function testOnceMatchesMultipleTokensInOrder(): void
    {
        $manager = $this->manager([
            $this->primitive(),
            $this->pipe(),
            $this->identifier(),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isPrimitive())
                ->once(fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testOnceFailsOnSecondTokenWhenFirstMatches(): void
    {
        $manager = $this->manager([$this->primitive(), $this->identifier()]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isPrimitive())
                ->once(fn($t) => $t->isPipe())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // separated (top-level — goes through match(), no bug)
    // -------------------------------------------------------------------------

    public function testSeparatedMatchesSingleItem(): void
    {
        $manager = $this->manager([$this->primitive(), $this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe())
                ->match()
        );
    }

    public function testSeparatedMatchesMultipleItemsWithSeparators(): void
    {
        $manager = $this->manager([
            $this->primitive('String'),
            $this->pipe(),
            $this->primitive('Int'),
            $this->pipe(),
            $this->primitive('Float'),
            $this->identifier('name'),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testSeparatedFailsWhenNoTokenMatches(): void
    {
        $manager = $this->manager([$this->identifier()]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe())
                ->match()
        );
    }

    public function testSeparatedStopsCleanlyWhenNoSeparatorFollows(): void
    {
        // String name — no pipe, separated stops after first match, once picks up identifier
        $manager = $this->manager([$this->primitive('String'), $this->identifier('name')]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // separated inside nested builder — regression for BUG-02 (executeSub)
    // -------------------------------------------------------------------------

    public function testSeparatedInsideGroupMatchesWithoutDoubleIncrement(): void
    {
        // Before the fix: double offset increment caused | to be skipped,
        // so Int was checked as separator (not a pipe), loop broke early,
        // and `name` ended up at the wrong offset — once(isIdentifier) failed.
        $manager = $this->manager([
            $this->primitive('String'),
            $this->pipe(),
            $this->primitive('Int'),
            $this->identifier('name'),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->group(fn($b) => $b->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testSeparatedInsideOptionalMatchesWithoutDoubleIncrement(): void
    {
        $manager = $this->manager([
            $this->primitive('String'),
            $this->pipe(),
            $this->primitive('Int'),
            $this->identifier('name'),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->optional(fn($b) => $b->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testSeparatedInsideOrMatchesWithoutDoubleIncrement(): void
    {
        $manager = $this->manager([
            $this->primitive('String'),
            $this->pipe(),
            $this->primitive('Int'),
            $this->identifier('name'),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->or(fn($b) => $b->separated(fn($t) => $t->isPrimitive(), fn($t) => $t->isPipe()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // optional
    // -------------------------------------------------------------------------

    public function testOptionalAdvancesOffsetWhenPatternMatches(): void
    {
        $manager = $this->manager([$this->pipe(), $this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->optional(fn($b) => $b->once(fn($t) => $t->isPipe()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testOptionalContinuesWithoutAdvancingWhenPatternDoesNotMatch(): void
    {
        // No pipe present — optional skips, once still sees identifier at offset 0
        $manager = $this->manager([$this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->optional(fn($b) => $b->once(fn($t) => $t->isPipe()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testOptionalDoesNotConsumeTokensOnMismatch(): void
    {
        // Optional looks for identifier (fails on primitive), primitive must still be at offset 0
        $manager = $this->manager([$this->primitive()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->optional(fn($b) => $b->once(fn($t) => $t->isIdentifier()))
                ->once(fn($t) => $t->isPrimitive())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // group
    // -------------------------------------------------------------------------

    public function testGroupSucceedsAndAdvancesOffset(): void
    {
        $manager = $this->manager([$this->primitive(), $this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->group(fn($b) => $b->once(fn($t) => $t->isPrimitive()))
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testGroupFailsWhenPatternDoesNotMatch(): void
    {
        $manager = $this->manager([$this->identifier()]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->group(fn($b) => $b->once(fn($t) => $t->isPrimitive()))
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // or
    // -------------------------------------------------------------------------

    public function testOrMatchesFirstAlternative(): void
    {
        $manager = $this->manager([$this->primitive()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->or(
                    fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    fn($b) => $b->once(fn($t) => $t->isIdentifier()),
                )
                ->match()
        );
    }

    public function testOrFallsThroughToSecondAlternative(): void
    {
        $manager = $this->manager([$this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->or(
                    fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    fn($b) => $b->once(fn($t) => $t->isIdentifier()),
                )
                ->match()
        );
    }

    public function testOrFailsWhenNoAlternativeMatches(): void
    {
        $manager = $this->manager([$this->number()]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->or(
                    fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    fn($b) => $b->once(fn($t) => $t->isIdentifier()),
                )
                ->match()
        );
    }

    public function testOrAdvancesOffsetByMatchedAlternativeLength(): void
    {
        // or matches identifier (1 token), then once sees pipe at offset 1
        $manager = $this->manager([$this->identifier(), $this->pipe()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->or(
                    fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    fn($b) => $b->once(fn($t) => $t->isIdentifier()),
                )
                ->once(fn($t) => $t->isPipe())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // skipUntil
    // -------------------------------------------------------------------------

    public function testSkipUntilStopsAtMatchingToken(): void
    {
        $manager = $this->manager([
            $this->identifier('x'),
            $this->colon(),
            $this->primitive(),
            $this->closeParen(),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->skipUntil(fn($t) => $t->isClosingParenthesis())
                ->once(fn($t) => $t->isClosingParenthesis())
                ->match()
        );
    }

    public function testSkipUntilReturnsFalseWhenLimitReached(): void
    {
        // 22 non-matching tokens — exceeds MAX_FORWARD (20)
        $tokens = array_fill(0, 22, $this->identifier());

        $this->assertFalse(
            $this->manager($tokens)->sequence()
                ->lookAhead()
                ->skipUntil(fn($t) => $t->isPrimitive())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // until (early stop)
    // -------------------------------------------------------------------------

    public function testUntilStopsBeforeEolToken(): void
    {
        $manager = $this->manager([
            $this->primitive(),
            $this->eol(),
            $this->identifier(),
        ]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isPrimitive())
                ->until(fn($t) => $t->isEndOfLine())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testUntilDoesNotStopWhenConditionNeverMet(): void
    {
        $manager = $this->manager([$this->primitive(), $this->identifier()]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isPrimitive())
                ->until(fn($t) => $t->isEndOfLine())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    public function testUntilAlsoStopsSeparated(): void
    {
        // EOL appears after String — separated should not continue past it
        $manager = $this->manager([
            $this->primitive('String'),
            $this->eol(),
            $this->identifier('name'),
        ]);

        // Without until: separated would fail at eol (not a type), matched=true, then once sees eol
        // With until: separated stops at eol before checking separator, then once fails on eol
        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isType(), fn($t) => $t->isPipe())
                ->until(fn($t) => $t->isEndOfLine())
                ->once(fn($t) => $t->isIdentifier())
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // around
    // -------------------------------------------------------------------------

    public function testAroundMatchesBothDirections(): void
    {
        // Tokens: [identifier(0), pipe(1), primitive(2), identifier(3)]
        // currentPosition = 2 → offset 0 = primitive(2), offset 1 = identifier(3)
        // backward once(isPrimitive): peeks offset 0 = primitive(2) → match
        // forward once(isPrimitive) + once(isIdentifier): offset 0 = primitive, offset 1 = identifier
        $manager = $this->manager(
            [$this->identifier(), $this->pipe(), $this->primitive(), $this->identifier()],
            currentPosition: 2
        );

        $this->assertTrue(
            $manager->sequence()
                ->around(
                    backward: fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    forward: fn($b) => $b
                        ->once(fn($t) => $t->isPrimitive())
                        ->once(fn($t) => $t->isIdentifier()),
                )
                ->match()
        );
    }

    public function testAroundFailsWhenBackwardPatternDoesNotMatch(): void
    {
        $manager = $this->manager(
            [$this->identifier(), $this->pipe(), $this->primitive(), $this->identifier()],
            currentPosition: 2
        );

        $this->assertFalse(
            $manager->sequence()
                ->around(
                    backward: fn($b) => $b->once(fn($t) => $t->isIdentifier()), // primitive at offset 0
                    forward: fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                )
                ->match()
        );
    }

    public function testAroundFailsWhenForwardPatternDoesNotMatch(): void
    {
        $manager = $this->manager(
            [$this->identifier(), $this->pipe(), $this->primitive(), $this->identifier()],
            currentPosition: 2
        );

        $this->assertFalse(
            $manager->sequence()
                ->around(
                    backward: fn($b) => $b->once(fn($t) => $t->isPrimitive()),
                    forward: fn($b) => $b->once(fn($t) => $t->isIdentifier()), // primitive at offset 0
                )
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // lookBehind
    // -------------------------------------------------------------------------

    public function testLookBehindMatchesPreviousTokens(): void
    {
        // Tokens: [identifier(0), primitive(1), pipe(2)]
        // currentPosition = 2 → offset 0 = pipe(2), offset -1 = primitive(1)
        $manager = $this->manager(
            [$this->identifier(), $this->primitive(), $this->pipe()],
            currentPosition: 2
        );

        $this->assertTrue(
            $manager->sequence()
                ->lookBehind()
                ->once(fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isPrimitive())
                ->match()
        );
    }

    public function testLookBehindFailsWhenPreviousTokenDoesNotMatch(): void
    {
        $manager = $this->manager(
            [$this->identifier(), $this->identifier(), $this->pipe()],
            currentPosition: 2
        );

        $this->assertFalse(
            $manager->sequence()
                ->lookBehind()
                ->once(fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isPrimitive()) // identifier at position 1
                ->match()
        );
    }

    // -------------------------------------------------------------------------
    // MAX_FORWARD safety limit
    // -------------------------------------------------------------------------

    public function testExceedingMaxForwardLimitReturnsFalse(): void
    {
        // MAX_FORWARD = 20; 21 once-rules consume 20 steps then hit the limit
        $tokens = array_fill(0, 25, $this->identifier());
        $seq = $this->manager($tokens)->sequence()->lookAhead();

        for ($i = 0; $i < 21; $i++) {
            $seq->once(fn($t) => $t->isIdentifier());
        }

        $this->assertFalse($seq->match());
    }

    // -------------------------------------------------------------------------
    // Real-world patterns
    // -------------------------------------------------------------------------

    public function testPropertyResolverPattern(): void
    {
        // String | Int | Float name
        $manager = $this->manager([
            $this->primitive('String'),
            $this->pipe(),
            $this->primitive('Int'),
            $this->pipe(),
            $this->primitive('Float'),
            $this->identifier('name'),
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->separated(fn($t) => $t->isType(), fn($t) => $t->isPipe())
                ->once(fn($t) => $t->isIdentifier())
                ->until(fn($t) => $t->isEndOfLine())
                ->match()
        );
    }

    public function testArrowFunctionResolverPattern(): void
    {
        // (Float price): Float =>
        $manager = $this->manager([
            $this->openParen(),      // 0
            $this->primitive(),      // 1
            $this->identifier('x'), // 2
            $this->closeParen(),    // 3
            $this->colon(),          // 4
            $this->primitive(),      // 5
            $this->arrow(),          // 6
        ]);

        $this->assertTrue(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isOpeningParenthesis())
                ->skipUntil(fn($t) => $t->isClosingParenthesis())
                ->once(fn($t) => $t->isClosingParenthesis())
                ->once(fn($t) => $t->isColon())
                ->skipUntil(fn($t) => $t->isArrow())
                ->once(fn($t) => $t->isArrow())
                ->match()
        );
    }

    public function testArrowFunctionResolverPatternFailsWithoutArrow(): void
    {
        $manager = $this->manager([
            $this->openParen(),
            $this->closeParen(),
            $this->colon(),
            $this->primitive(),
            $this->identifier(), // not =>
        ]);

        $this->assertFalse(
            $manager->sequence()
                ->lookAhead()
                ->once(fn($t) => $t->isOpeningParenthesis())
                ->skipUntil(fn($t) => $t->isClosingParenthesis())
                ->once(fn($t) => $t->isClosingParenthesis())
                ->once(fn($t) => $t->isColon())
                ->skipUntil(fn($t) => $t->isArrow())
                ->once(fn($t) => $t->isArrow())
                ->match()
        );
    }
}
