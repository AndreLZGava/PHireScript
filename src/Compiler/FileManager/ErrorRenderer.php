<?php

declare(strict_types=1);

namespace PHireScript\Compiler\FileManager;

use PHireScript\Helper\Messenger;
use PHireScript\Runtime\Exceptions\CompileException;
use Throwable;

class ErrorRenderer
{
    public function renderCli(Throwable $e, mixed $transpiler, mixed $code): void
    {
        $width = (int) shell_exec('tput cols') ?: 120;

        $gutterWidth    = 10;
        $availableWidth = $width - $gutterWidth;

        $red   = "\033[1;31m";
        $blue  = "\033[1;34m";
        $cyan  = "\033[1;36m";
        $gray  = "\033[0;90m";
        $reset = "\033[0m";

        $codeGenerated = $transpiler->getCodeBeforeGenerator();
        $hasTranspiled = !empty(\trim((string) $codeGenerated));

        $originalLines  = \explode("\n", \rtrim((string) $code));
        $preParserLines = $hasTranspiled ? \explode("\n", \rtrim((string) $codeGenerated)) : [];
        $maxLines       = \max(\count($originalLines), \count($preParserLines));
        $errorLine      = 0;
        $message        = $e->getMessage();

        if ($e instanceof CompileException) {
            $errorLine = $e->line;
        }

        Messenger::banner('orange', "PHire Script DEBUGGER - COMPILATION ERROR");
        Messenger::text("");

        if ($hasTranspiled) {
            $colWidth = (int) ($availableWidth / 2) - 2;
            \printf(
                " %-4s | %-{$colWidth}s | %s\n",
                "Line",
                "{$blue}ORIGINAL PHire Script{$reset}",
                "{$cyan}TRANSPILED PHP{$reset}"
            );
        } else {
            $colWidth = $availableWidth;
            \printf(" %-4s | %s\n", "Line", "{$blue}ORIGINAL PHire Script (Full View){$reset}");
        }

        echo \str_repeat('-', $width) . "\n";

        for ($i = 0; $i < $maxLines; $i++) {
            $currentLineNum = $i + 1;
            $left  = $originalLines[$i] ?? '';
            $right = $preParserLines[$i] ?? '';

            $indicator    = ($currentLineNum === $errorLine) ? "{$red}→{$reset}" : " ";
            $lineNumColor = ($currentLineNum === $errorLine) ? $red : $gray;

            if ($hasTranspiled) {
                \printf(
                    " %s %s%-3d%s | %s%-{$colWidth}s%s | %s%s%s\n",
                    $indicator,
                    $lineNumColor,
                    $currentLineNum,
                    $reset,
                    $blue,
                    \mb_substr($left, 0, $colWidth),
                    $reset,
                    $cyan,
                    \mb_substr($right, 0, $colWidth),
                    $reset
                );
            } else {
                \printf(
                    " %s %s%-3d%s | %s%s%s\n",
                    $indicator,
                    $lineNumColor,
                    $currentLineNum,
                    $reset,
                    $blue,
                    $left,
                    $reset
                );
            }
        }

        Messenger::error("ERROR MESSAGE", true);
        Messenger::warning("» {$message}");
        Messenger::text(str_repeat('─', $width));
    }

    public function renderWeb(Throwable $e, mixed $transpiler, mixed $code): never
    {
        $width          = 120;
        $gutterWidth    = 8;
        $availableWidth = $width - $gutterWidth;

        $codeGenerated  = $transpiler->getCodeBeforeGenerator();
        $hasTranspiled  = !empty(\trim((string) $codeGenerated));

        $originalLines  = \explode("\n", \rtrim((string) $code));
        $preParserLines = $hasTranspiled ? \explode("\n", \rtrim((string) $codeGenerated)) : [];
        $maxLines       = \max(\count($originalLines), \count($preParserLines));

        $message   = htmlspecialchars((string) $e->getMessage());
        $errorLine = ($e instanceof CompileException) ? $e->line : null;

        $html  = '';
        $html .= '<pre style="
        background:#0d1117;
        color:#c9d1d9;
        padding:20px;
        border-radius:10px;
        font-family: Consolas, monospace;
        font-size:14px;
        overflow:auto;
    ">';

        $html .= "PHire Script DEBUGGER - COMPILATION ERROR\n";
        $html .= \str_repeat('=', $width) . "\n\n";

        if ($hasTranspiled) {
            $colWidth = (int) ($availableWidth / 2) - 2;
            $html    .= \str_pad('Line', 6) .
                \str_pad('ORIGINAL PHire Script', $colWidth) .
                " | TRANSPILED PHP\n";
        } else {
            $colWidth = $availableWidth;
            $html    .= \str_pad('Line', 6) . "ORIGINAL PHire Script\n";
        }

        $html .= \str_repeat('-', $width) . "\n";

        for ($i = 0; $i < $maxLines; $i++) {
            $currentLineNum = $i + 1;
            $left  = \htmlspecialchars($originalLines[$i] ?? '');
            $right = \htmlspecialchars($preParserLines[$i] ?? '');

            $isError    = ($currentLineNum === $errorLine);
            $linePrefix = str_pad((string) $currentLineNum, 4, ' ', STR_PAD_LEFT) . ' ';

            if ($hasTranspiled) {
                $line =
                    $linePrefix .
                    str_pad(mb_substr($left, 0, $colWidth), $colWidth) .
                    " | " .
                    mb_substr($right, 0, $colWidth);
            } else {
                $line = $linePrefix . $left;
            }

            if ($isError) {
                $html .= '<span style="background:#3b0d0d;color:#ff7b72;"> → ' . $line . "</span>\n";
            } else {
                $html .= '  ' . $line . "\n";
            }
        }

        $html .= "\n\nERROR MESSAGE:\n";
        $html .= '<span style="color:#ff7b72;">» ' . $message . "</span>\n";
        $html .= str_repeat('=', $width);
        $html .= '</pre>';

        echo $html;
        exit;
    }

    public function renderExecution(string $compiledCode, string $executionResult): void
    {
        $compiledSafe = htmlspecialchars($compiledCode);
        $resultSafe   = htmlspecialchars($executionResult);

        echo '
    <div style="
        display:flex;
        gap:20px;
        align-items:flex-start;
        font-family:Consolas, monospace;
    ">
        <div style="flex:1;">
            <h3 style="margin:0 0 10px 0;color:#58a6ff;">PHire Script</h3>
            <pre style="
                background:#0d1117;
                color:#c9d1d9;
                padding:20px;
                border-radius:10px;
                overflow:auto;
                min-height:400px;
            ">' . $compiledSafe . '</pre>
        </div>

        <div style="flex:1;">
            <h3 style="margin:0 0 10px 0;color:#3fb950;">PHP result</h3>
            <pre style="
                background:#0d1117;
                color:#c9d1d9;
                padding:20px;
                border-radius:10px;
                overflow:auto;
                min-height:400px;
            ">' . $resultSafe . '</pre>
        </div>
    </div>
    ';
    }
}
