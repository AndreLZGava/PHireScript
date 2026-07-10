<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use Exception;
use PHireScript\Compiler\Parser\Managers\Builder\SequenceBuilder;
use PHireScript\Compiler\Parser\Managers\Token\Token;

class TokenManager
{
    private const DEFAULT_TOKEN_WINDOW = 100;

    private $tokenLookup;
    public $positionLookup;

    private Token $currentToken;

    private $endFileToken;

    public function __construct(private readonly string $context, private array $tokens, private int $currentPosition)
    {
        $this->endFileToken = new Token(
            type: 'T_EOF',
            value: '',
            line: 0,
            column: 0,
        );

        $this->currentToken = $this->tokens[$this->currentPosition] ?? $this->endFileToken;
        $this->tokenLookup = $this->currentToken;
        $this->positionLookup = $this->currentPosition;
    }

    public function getLeftTokens(int $limit = self::DEFAULT_TOKEN_WINDOW): array
    {
        return array_slice($this->getTokens(), $this->getCurrentPosition(), $limit);
    }

    public function getProcessedTokens(int $limit = self::DEFAULT_TOKEN_WINDOW): array
    {
        $start = (int) max(0, $this->getCurrentPosition() - $limit);
        return array_slice($this->getTokens(), $start, $limit);
    }

    public function getAll()
    {
        return [
            'context' => $this->getContext(),
            'currentPosition' => $this->getCurrentPosition(),
            'positionLookup' => $this->positionLookup,
            'previous' => $this->getPreviousTokenBeforeCurrent(),
            'currentToken' => $this->getCurrentToken(),
            'next' => $this->getNextTokenAfterCurrent(),
            'tokens' => $this->getLeftTokens()
        ];
    }

    public function getNextAfterFirstFoundElement($elementsAsValue): ?Token
    {
        $count = \count($this->tokens);
        for ($i = $this->currentPosition; $i < $count; $i++) {
            if (\in_array($this->tokens[$i]->value, $elementsAsValue, true)) {
                return $this->tokens[$i + 1] ?? null;
            }
        }
        return null;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getCurrentPosition()
    {
        return $this->currentPosition;
    }

    public function getTokens()
    {
        return $this->tokens;
    }

    public function advance()
    {
        $this->currentPosition++;

        if ($this->currentPosition < \count($this->tokens)) {
            $this->currentToken = $this->tokens[$this->currentPosition];
        } else {
            $this->currentToken = $this->endFileToken;
        }

        $this->tokenLookup = $this->currentToken;
        $this->positionLookup = $this->currentPosition;
    }

    public function walk($positions)
    {
        $this->currentPosition += $positions;
        if (!$this->isEndOfTokens() && isset($this->tokens[$this->currentPosition + 1])) {
            $this->currentToken = $this->tokens[$this->currentPosition];
            $this->tokenLookup = $this->currentToken;
            $this->positionLookup = $this->currentPosition;
        }
    }

    public function isEndOfTokens(): bool
    {
        return $this->currentPosition >= \count($this->tokens);
    }

    public function setCurrentPosition(int $position)
    {
        $this->currentPosition = $position;
    }

    public function getNextToken()
    {
        $this->positionLookup++;
        $this->tokenLookup = $this->tokens[$this->positionLookup] ?? $this->endFileToken;
        return $this->tokenLookup;
    }

    public function getCurrentToken()
    {
        return $this->currentToken ?? $this->endFileToken;
    }

    public function getPreviousTokenBeforeCurrent()
    {
        $this->positionLookup = $this->currentPosition;
        return $this->getPreviousToken();
    }

    public function getNextTokenAfterCurrent()
    {
        $this->positionLookup = $this->currentPosition;
        return $this->getNextToken();
    }

    public function getPreviousToken()
    {
        $this->positionLookup--;
        $this->tokenLookup = $this->tokens[$this->positionLookup] ?? $this->endFileToken;
        return $this->tokenLookup;
    }

    public function peek(int $offset = 0): Token
    {
        $position = $this->currentPosition + $offset;
        return $this->tokens[$position] ?? $this->endFileToken;
    }

    public function matchSequence(array $rules, callable $until): bool
    {
        $offset = 0;

        foreach ($rules as $rule) {
            $type = $rule['type'];

            if ($type === 'once') {
                $token = $this->peek($offset);

                if ($until($token) || !$rule['match']($token)) {
                    return false;
                }

                $offset++;
            }

            if ($type === 'separated') {
                $matched = false;

                while (true) {
                    $token = $this->peek($offset);

                    if ($until($token) || !$rule['match']($token)) {
                        break;
                    }

                    $matched = true;
                    $offset++;

                    $separatorToken = $this->peek($offset);

                    if ($rule['separator']($separatorToken)) {
                        $offset++;
                        continue;
                    }

                    break;
                }

                if (!$matched) {
                    return false;
                }
            }
        }

        return true;
    }

    public function sequence(): SequenceBuilder
    {
        return new SequenceBuilder($this);
    }
}
