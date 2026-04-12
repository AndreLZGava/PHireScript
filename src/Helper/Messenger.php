<?php

declare(strict_types=1);

namespace PHireScript\Helper;

/**
 * Messenger utility for styled CLI/Web output.
 * Supports colors, icons and semantic messages (success, error, warning, etc).
 */
class Messenger
{
    private static bool $isCli;

    /**
     * ANSI color map for CLI
     */
    private static array $colors = [
        'default' => "\033[1;37m",
        'success' => "\033[1;32m",
        'error'   => "\033[1;31m",
        'warning' => "\033[1;33m",
        'info'    => "\033[1;34m",
        'muted'   => "\033[0;90m",
        'reset'   => "\033[0m",
        'gray' => "\033[90m",
        'blue' => "\033[36m",
        'yellow' => "\033[33m",
        'orange' => "\033[1;38;2;255;165;0m"
    ];

    /**
     * Icons map
     */
    private static array $icons = [
        'success' => '✔',
        'error'   => '✖',
        'warning' => '⚠',
        'info'    => 'ℹ',
        'default' => '•',
    ];

    /**
     * Initialize environment detection
     */
    private static function init(): void
    {
        self::$isCli = (php_sapi_name() === 'cli');
    }

    /**
     * Generic renderer
     */
    private static function render(
        string $message,
        string $type = 'default',
        bool $useIcon = false
    ): void {
        self::init();

        $icon = $useIcon ? (self::$icons[$type] ?? self::$icons['default']) . ' ' : '';

        if (self::$isCli) {
            $color = self::$colors[$type] ?? self::$colors['default'];
            $reset = self::$colors['reset'];

            echo "{$color}{$icon}{$message}{$reset}\n";
        } else {
            echo self::renderWeb($message, $type, $icon);
        }
    }

    /**
     * Web renderer (HTML)
     */
    private static function renderWeb(string $message, string $type, string $icon): string
    {
        $colorMap = [
            'success' => '#16a34a',
            'error'   => '#dc2626',
            'warning' => '#d97706',
            'info'    => '#2563eb',
            'default' => '#333333',
        ];

        $color = $colorMap[$type] ?? $colorMap['default'];

        return "<div style='
            font-family: monospace;
            padding: 6px 10px;
            margin: 4px 0;
            border-left: 4px solid {$color};
            color: {$color};
            background: #f9fafb;
        '>{$icon}" . htmlspecialchars($message) . "</div>";
    }

    /**
     * SUCCESS message (green ✔)
     */
    public static function success(string $message, bool $useIcon = false): void
    {
        self::render($message, 'success', $useIcon);
    }

    /**
     * ERROR message (red ✖)
     */
    public static function error(string $message, bool $useIcon = false): void
    {
        self::render($message, 'error', $useIcon);
    }

    /**
     * WARNING message (yellow ⚠)
     */
    public static function warning(string $message, bool $useIcon = false): void
    {
        self::render($message, 'warning', $useIcon);
    }

    /**
     * INFO message (blue ℹ)
     */
    public static function info(string $message, bool $useIcon = false): void
    {
        self::render($message, 'info', $useIcon);
    }

    /**
     * DEFAULT message (white)
     */
    public static function text(string $message, bool $useIcon = false): void
    {
        self::render($message, 'default', $useIcon);
    }

    /**
     * Custom color output (advanced usage)
     */
    public static function custom(string $message, string $ansiColor, bool $useIcon = false): void
    {
        self::init();

        $icon = $useIcon ? self::$icons['default'] . ' ' : '';

        if (self::$isCli) {
            echo "{$ansiColor}{$icon}{$message}" . self::$colors['reset'] . "\n";
        } else {
            echo self::renderWeb($message, 'default', $icon);
        }
    }

    public static function banner(string $type, string $title): void
    {
        $width = (int) shell_exec('tput cols') ?: 120;

        self::text("");

        self::render(\str_repeat('=', $width), $type);
        self::render("  {$title}", $type);
        self::render(\str_repeat('=', $width), $type);

        self::text("");
    }

    public static function trace(
        array $gotTrace
    ): void {
        $gray = self::$colors['gray'];
        $blue = self::$colors['blue'];
        $yellow = self::$colors['yellow'];
        $reset = self::$colors['reset'];

        foreach ($gotTrace as $i => $trace) {
            $file = $trace['file'] ?? '[internal]';
            $line = $trace['line'] ?? '-';
            $class = $trace['class'] ?? '';
            $type = $trace['type'] ?? '';
            $function = $trace['function'] ?? '';

            $shortFile = \basename($file);

            echo "{$gray}#$i{$reset} ";
            echo "{$blue}{$shortFile}{$reset}:{$yellow}{$line}{$reset} → ";
            echo "{$class}{$type}{$function}()\n";
        }
    }
}
