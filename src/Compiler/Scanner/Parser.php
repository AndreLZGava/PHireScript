<?php

namespace PHPScript\Compiler\Scanner;

use PHPScript\Compiler\Scanner\Program;
use PHPScript\Compiler\Scanner\ClassDefinition;
use PHPScript\Compiler\Scanner\MethodDefinition;
use PHPScript\Compiler\Scanner\PropertyDefinition;
use PHPScript\Compiler\Scanner\GlobalStatement;

class Parser {
  private array $tokens;
  private int $pos = 0;

  public function __construct(array $tokens) {
    $this->tokens = $tokens;
  }

  public function parse(): Program {
    $program = new Program();
    $lastComment = null;

    while (!$this->isEOF()) {
      $startPos = $this->pos;
      $token = $this->current();
      if ($token['type'] === 'T_COMMENT') {
        $lastComment = $this->consume()['value'];
        continue;
      }

      var_dump($token);exit;
      if ($token['type'] === 'T_EOL') {
        $this->advance();
        continue;
      }

      if ($token['type'] === 'T_KEYWORD' && in_array($token['value'], ['class', 'interface', 'trait', 'type'])) {
        $classNode = $this->parseClassLike();
        $classNode->docBlock = $lastComment;
        $program->statements[] = $classNode;
        $lastComment = null;
      } else {
        $statement = $this->parseGlobalStatement();
        if ($statement) {
          $program->statements[] = $statement;
        }
        $lastComment = null;
      }


      if ($this->pos === $startPos) {

        $this->advance();
      }
    }

    return $program;
  }

  private function parseClassLike(): ClassDefinition {
    $node = new ClassDefinition();
    $node->type = $this->consume()['value'];
    $node->name = $this->consume('T_IDENTIFIER')['value'];
    $contextType = $node->type;

    while (!$this->isEOF() && $this->current()['value'] !== '{') {
      $val = $this->consume()['value'];
      if ($val === 'extends') $node->extends = $this->consume('T_IDENTIFIER')['value'];
      if ($val === 'with') {
        do {
          $node->mixins[] = $this->consume('T_IDENTIFIER')['value'];
        } while ($this->optional(','));
      }
      if ($val === 'implements') {
        do {
          $node->implements[] = $this->consume('T_IDENTIFIER')['value'];
        } while ($this->optional(','));
      }
    }

    $this->consume('T_SYMBOL', '{');

    while (!$this->isEOF() && $this->current()['value'] !== '}') {
      $startPos = $this->pos;

      if ($this->current()['type'] === 'T_EOL' || $this->current()['type'] === 'T_COMMENT') {
        $this->advance();
        continue;
      }

      $member = $this->parseClassMember($contextType);
      if ($member) {
        $node->body[] = $member;
      }


      if ($this->pos === $startPos) {
        throw new \Exception("Loop detectado no membro da classe: " . $this->current()['value'] . " na linha " . $this->current()['line']);
      }
    }

    $this->consume('T_SYMBOL', '}');
    return $node;
  }

  private function parseClassMember(string $contextType) {


    while (!$this->isEOF() && ($this->current()['type'] === 'T_EOL' || $this->current()['type'] === 'T_COMMENT')) {
        $this->advance();
    }

    if ($this->isEOF() || $this->current()['value'] === '}') return null;

    $modifiers = [];
    $validModifiers = ['#', '+', '<', '>', '<>', '+>', 'inject', 'async', 'static', 'readonly'];
    while (in_array($this->current()['value'], $validModifiers)) {
        $modifiers[] = $this->consume()['value'];
    }


    if ($this->current()['type'] !== 'T_IDENTIFIER' && $this->current()['value'] !== 'constructor') {

        $this->advance();
        return null;
    }


    $isMethod = false;
    $lookahead = 0;
    while (isset($this->tokens[$this->pos + $lookahead])) {
      $t = $this->tokens[$this->pos + $lookahead];
      if ($t['value'] === '(') {
        $isMethod = true;
        break;
      }

      if ($t['type'] === 'T_EOL' || $t['value'] === ';' || $t['value'] === '}') break;
      $lookahead++;
      if ($lookahead > 10) break;
    }

    if ($isMethod) {
      return $this->parseMethod($modifiers, false, $contextType);
    } else {
      $prop = new PropertyDefinition();
      $prop->modifiers = $modifiers;


      $first = $this->parseType();

      if ($this->current()['type'] === 'T_IDENTIFIER') {
        $prop->type = $first;
        $prop->name = $this->consume('T_IDENTIFIER')['value'];
      } else {
        $prop->name = $first;
      }

      if ($this->optional('=')) {
        $prop->defaultValue = $this->consumeUntilNewlineOrSemicolon();
      }
      $this->consumeEndOfStatement();

      return $prop;
    }
  }

  private function consumeUntilNewlineOrSemicolon() {
    $content = "";

    while (
      !$this->isEOF() &&
      $this->current()['value'] !== ';' &&
      $this->current()['value'] !== '}'
    ) {
      $content .= $this->consume()['value'] . " ";
    }
    return trim($content);
  }

  private function parseMethod(array $modifiers, bool $isConstructor, string $contextType) {
    $method = new MethodDefinition();
    $method->modifiers = $modifiers;

    if ($isConstructor) {
      $this->consume('T_KEYWORD', 'constructor');
      $method->name = '__construct';
    } else {
      $first = $this->consume('T_IDENTIFIER')['value'];
      if ($this->current()['type'] === 'T_IDENTIFIER') {
        $method->returnType = $first;
        $method->name = $this->consume('T_IDENTIFIER')['value'];
      } else {
        $method->name = $first;
      }


      if (in_array($this->current()['value'], ['!', '?'])) {
        $method->name .= $this->consume()['value'];
      }
    }

    $this->consume('T_SYMBOL', '(');
    while ($this->current()['value'] !== ')') {
      $this->advance();
    }
    $this->consume('T_SYMBOL', ')');
    if ($this->optional(':')) {
      $method->returnType = $this->consume('T_IDENTIFIER')['value'];
    }

    if ($contextType === 'interface') {
      $method->bodyCode = '';
      $this->consumeEndOfStatement();
      return $method;
    }

    if ($this->optional('=>')) {
      $method->bodyCode = "return " . $this->consumeUntil(';') . ";";
    } else {
      $method->bodyCode = $this->captureBlock();
    }

    return $method;
  }

  private function parseGlobalStatement(): GlobalStatement {
    $node = new GlobalStatement();
    $code = "";

    while (!$this->isEOF()) {
      $t = $this->current();

      if ($t['type'] === 'T_COMMENT') break;
      if ($t['type'] === 'T_KEYWORD' && in_array($t['value'], ['class', 'type', 'interface', 'trait'])) break;

      $code .= $this->consume()['value'] . " ";

      if ($t['value'] === ';' || $t['type'] === 'T_EOL') break;
    }

    $node->code = trim($code);
    return $node;
  }
  private function isEOF(): bool {
    return $this->pos >= count($this->tokens);
  }

  private function current(): array {
    return $this->tokens[$this->pos] ?? [
      'type' => 'T_EOF',
      'value' => '',
      'line' => $this->line ?? 0,
      'column' => 0
    ];
  }

  private function advance() {
    if (!$this->isEOF()) {
      $this->pos++;
    }
  }
  private function consume($type = null, $value = null) {
    $token = $this->current();
    if ($type && $token['type'] !== $type) throw new \Exception("Waiting $type ($value), found {$token['type']}({$token['value']} line: {$token['line']} and column: {$token['column']}) ");
    if ($value && $token['value'] !== $value) throw new \Exception("Waiting $value, found {$token['value']}");
    $this->advance();
    return $token;
  }
  private function optional($value) {
    if ($this->current()['value'] === $value) {
      $this->advance();
      return true;
    }
    return false;
  }
  private function captureBlock() {
    $this->consume('T_SYMBOL', '{');
    $depth = 1;
    $content = "";


    while ($depth > 0 && !$this->isEOF()) {
      $token = $this->current();
      $val = $token['value'];

      if ($val === '{') $depth++;
      if ($val === '}') $depth--;

      if ($depth > 0) {
        $content .= $val . " ";
        $this->advance();
      }
    }

    if ($this->isEOF() && $depth > 0) {
      throw new \Exception("Syntax error: Block don't close. Expected '}'");
    }

    $this->consume('T_SYMBOL', '}');
    return $content;
  }
  private function consumeUntil($char) {
    $content = "";
    while ($this->current()['value'] !== $char) {
      $content .= $this->consume()['value'] . " ";
    }
    $this->consume(null, $char);
    return $content;
  }

  private function consumeEndOfStatement(): void {
    $found = false;

    while (!$this->isEOF()) {
      $token = $this->current();

      if ($token['type'] === 'T_EOL' || $token['value'] === ';') {
        $this->advance();
        $found = true;
        continue;
      }

      if ($token['type'] === 'T_COMMENT') {

        if (str_starts_with($token['value'], '//')) $found = true;
        $this->advance();
        continue;
      }
      break;
    }

    if (!$found && $this->current()['value'] !== '}') {

      $token = $this->current();
      $this->advance();
      throw new \Exception("Syntax error in line {$token['line']}: Waiting for instruction end.");
    }
  }

  private function parseType(): string {
    if ($this->current()['type'] !== 'T_IDENTIFIER') {
      return '';
    }

    $type = $this->consume('T_IDENTIFIER')['value'];

    while ($this->current()['value'] === '|') {
      $this->advance();
      $type .= '|' . $this->consume('T_IDENTIFIER')['value'];
    }

    return $type;
  }
}
