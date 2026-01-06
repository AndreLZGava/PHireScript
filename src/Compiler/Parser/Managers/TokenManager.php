<?php

namespace PHPScript\Compiler\Parser\Managers;

class TokenManager {


  private $tokens;
  private $tokenLookup;
  private $positionLookup;

  private $currentToken;
  private $currentPosition;

  private $context;

  private $endFileToken;

  public function __construct(string $context, array $tokens, int $position) {
    $this->context = $context;

    $this->tokens = $tokens;

    $this->currentPosition = $position;
    $this->currentToken = $tokens[$position];

    $this->tokenLookup = $this->currentToken;
    $this->positionLookup = $this->currentPosition;

    $this->endFileToken = [
      'type' => 'T_EOF',
      'value' => '',
      'line' => $this->line ?? 0,
      'column' => 0
    ];
  }

  public function getContext() {
    return $this->context;
  }

  public function getCurrentPosition() {
    return $this->currentPosition;
  }

  public function getTokens() {
    return $this->tokens;
  }

  public function advance() {
    $this->currentPosition++;
    if (!$this->isEndOfTokens() && isset($this->tokens[$this->currentPosition + 1])) {
      $this->currentToken = $this->tokens[$this->currentPosition];
      $this->tokenLookup = $this->currentToken;
      $this->positionLookup = $this->currentPosition;
    }
  }

  public function walk($positions) {
    $this->currentPosition += $positions;
    $this->positionLookup += $positions;
  }

  public function isEndOfTokens(): bool {
    return $this->currentPosition >= count($this->tokens);
  }

  public function setCurrentPosition(int $position) {
    $this->currentPosition = $position;
  }

  public function getNextToken() {
    $this->positionLookup++;
    $this->tokenLookup = $this->tokens[$this->positionLookup] ?? $this->endFileToken;
    return $this->tokenLookup;
  }

  public function getCurrentToken() {
    return $this->currentToken ?? $this->endFileToken;
  }

  public function getPreviousTokenBeforeCurrent() {
    $this->positionLookup = $this->currentPosition;
    return $this->getPreviousToken();
  }

  public function getNextTokenAfterCurrent() {
    $this->positionLookup = $this->currentPosition;
    return $this->getNextToken();
  }

  public function getPreviousToken() {
    $this->positionLookup--;
    $this->tokenLookup = $this->tokens[$this->positionLookup] ?? $this->endFileToken;
    return $this->tokenLookup;
  }
}
