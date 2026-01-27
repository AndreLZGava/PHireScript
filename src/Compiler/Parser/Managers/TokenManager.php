<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Managers\Token\Token;

class TokenManager
{
    private $tokenLookup;
    public $positionLookup;

    private Token $currentToken;

    private $endFileToken;

    public function __construct(private readonly string $context, private array $tokens, private int $currentPosition)
    {
        $this->currentToken = $this->tokens[$this->currentPosition];

        $this->tokenLookup = $this->currentToken;
        $this->positionLookup = $this->currentPosition;

        $this->endFileToken = new Token(
            type: 'T_EOF',
            value: '',
            line: $this->line ?? 0,
            column: 0,
        );
    }

    public function getLeftTokens(int $limit = 100): array
    {
        return array_slice($this->getTokens(), $this->getCurrentPosition(), $limit);
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
        //$this->positionLookup++;
        if (!$this->isEndOfTokens() && isset($this->tokens[$this->currentPosition + 1])) {
            $this->currentToken = $this->tokens[$this->currentPosition];
            $this->tokenLookup = $this->currentToken;
            $this->positionLookup = $this->currentPosition;
        }
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
        return $this->currentPosition >= count($this->tokens);
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
}
