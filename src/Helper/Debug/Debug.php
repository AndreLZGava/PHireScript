<?php

declare(strict_types=1);

namespace PHireScript\Helper\Debug;

class Debug
{
    private static int $callCount = 0;

    public static function show(...$args): void
    {
        self::$callCount++;
        $isCli = (php_sapi_name() === 'cli');

        $backtrace = debug_backtrace();
        $file = $backtrace[0]['file'] ?? 'unknown';
        $line = $backtrace[0]['line'] ?? 0;

        $isAlt = self::$callCount % 2 === 0;

        foreach ($args as $index => $val) {
            $type = gettype($val);
            $displayValue = self::formatValue($val);

            $isArgAlt = ($index % 2 === 0) ? $isAlt : !$isAlt;

            if ($isCli) {
                self::renderCli($displayValue, $type, $file, $line, $isArgAlt);
            } else {
                self::renderWeb($displayValue, $type, $file, $line, $isArgAlt);
            }
        }
    }

    private static function formatValue($val): string
    {
        if (is_array($val) || is_object($val)) {
            return print_r($val, true);
        }
        if (is_bool($val)) {
            return $val ? 'true' : 'false';
        }
        return (string)$val;
    }

    private static function renderCli($val, $type, $file, $line, $alt): void
    {
        $color = $alt ? "\033[1;37m" : "\033[0;90m";
        $reset = "\033[0m";
        $header = "\033[0;33m[$file : $line]\033[0m";

        echo "$header $color($type)$reset $color$val$reset\n";
    }

    private static function renderWeb($val, $type, $file, $line, $alt): void
    {
        $bgColor = $alt ? "#ffffff" : "#f0f0f0";
        $textColor = $alt ? "#000000" : "#666666";

        echo "<pre style='background: $bgColor; color: $textColor; " .
            "margin: 0; padding: 5px; border-left: 3px solid #ffcc00;" .
            " font-family: monospace;'>";
        echo "<b style='color: #a52a2a;'>[$file : $line]</b> ";
        echo "<i style='opacity: 0.7;'>($type)</i> ";
        echo htmlspecialchars((string) $val);
        echo "</pre>";
    }
}
