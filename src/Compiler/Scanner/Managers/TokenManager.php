<?php

namespace PHPScript\Compiler\Scanner\Managers;

class TokenManager {
  private $tokens;
  private $tokenLookup;
  private $positionLookup;

  private $currentToken;
  private $currentPosition;

  private $context;

  public function __construct(string $context, array $tokens, int $position) {
    $this->context = $context;
    $this->tokens = $tokens;
    $this->currentPosition = $position;
    $this->currentToken = $tokens[$position];
    $this->tokenLookup = $this->currentToken;
    $this->positionLookup = $this->currentPosition;
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
    if (!$this->isEndOfTokens()) {
      $this->currentPosition++;
      $this->currentToken = $this->tokens[$this->currentPosition];
    }
  }

  public function isEndOfTokens(): bool {
    return $this->currentPosition >= count($this->tokens);
  }

  public function setCurrentPosition(int $position) {
    $this->currentPosition = $position;
  }

  public function getNextToken() {
    $this->positionLookup++;
    $this->tokenLookup = $this->tokens[$this->positionLookup];
    return $this->tokenLookup;
  }

  public function getCurrentToken() {
    return $this->currentToken ?? [
      'type' => 'T_EOF',
      'value' => '',
      'line' => $this->line ?? 0,
      'column' => 0
    ];
  }

  public function getPreviousToken() {
    $this->positionLookup--;
    $this->tokenLookup = $this->tokens[$this->positionLookup];
    return $this->tokenLookup;
  }
}
