<?php

function getErrorInterface($e, $transpiler, $code) {
  $maxLineWidth = 140;
  $red    = "\033[1;31m";
  $blue   = "\033[1;34m";
  $cyan   = "\033[1;36m";
  $gray   = "\033[0;90m";
  $yellow = "\033[1;33m";
  $reset  = "\033[0m";

  $originalLines = explode("\n", rtrim($code));
  $preParserLines = explode("\n", rtrim($transpiler->getCodeBeforeGenerator()));
  $maxLines = max(count($originalLines), count($preParserLines));

  echo "\n{$red}" . str_repeat('=', $maxLineWidth) . "{$reset}\n";
  echo "  {$red}PHPSCRIPT DEBUGGER - COMPILATION ERROR{$reset}\n";
  echo "{$red}" . str_repeat('=', $maxLineWidth) . "{$reset}\n\n";

  printf(
    " %-4s | %-71s | %-60s\n",
    "Line",
    "{$blue}ORIGINAL PHPSCRIPT{$reset}",
    "{$cyan}TRANSPILED PHP (PRE-PARSER){$reset}"
  );

  echo str_repeat('-', $maxLineWidth) . "\n";
  $message = $e->getMessage();
  $items = explode('on line ', $message);
  $line = (int) end($items);

  for ($i = 0; $i < $maxLines; $i++) {
    $lineNum = $i + 1;
    $left  = $originalLines[$i] ?? '';
    $right = $preParserLines[$i] ?? '';

    $originalColor = $blue;
    $compiledColor = $cyan;
    $lineNumColor  = $gray;

    $indicator = $line === $lineNum ? $red . "→" . $gray : " ";

    printf(
      " %s%s%-3d%s | %s%-60s%s | %s%-60s%s\n",
      $indicator,
      $lineNumColor,
      $lineNum,
      $reset,
      $originalColor,
      mb_substr($left, 0, 60),
      $reset,
      $compiledColor,
      mb_substr($right, 0, 60),
      $reset
    );
  }

  echo "\n{$yellow}ERROR MESSAGE:{$reset}\n";
  echo "{$red}» {$e->getMessage()}{$reset}\n";
  echo "{$red}" . str_repeat('=', $maxLineWidth) . "{$reset}\n";
}
