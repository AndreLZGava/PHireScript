<?php

namespace PHPScript\Compiler;

class Scanner
{
    private string $code;
    private int $cursor = 0;
    private int $line = 1;
    private int $lineStartOffset = 0;

    private const PATTERNS = [
        'T_COMMENT'     => '/^\/\/.*|^\/\*[\s\S]*?\*\//',
        'T_STRING_LIT'  => '/^"([^"\\\\]*(?:\\\\.[^"\\\\]*)*)"|^\'([^\'\\\\]*(?:\\\\.[^\'\\\\]*)*)\'/',
        'T_NUMBER'      => '/^\d+(\.\d+)?/',
        'T_KEYWORD'     => '/^\b(class|interface|trait|type|extends|with|' .
            'implements|inject|async|var|constructor|function|return|echo|' .
            'if|else|this|super)\b/',
        'T_BOOL'        => '/^\b(true|false)\b/',
        'T_EOL'         => '/^[\r\n]+/',
        'T_WHITESPACE'  => '/^[ \t]+/',
        'T_MODIFIER'    => '/^(\+>|\<>|\#>|\->|=>|::|\.\.\.|\+\+|--|==|!=|<=|>=|&&|\|\|)/',
        'T_TYPE'        => '/^\b(Int|String|Float|Bool|Object|Array|Void|' .
            'Mixed|Any|Date|DateTime|Time|Email|Ipv4|Ipv6)\b/',
        'T_VARIABLE'    => '/^\$[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
        'T_IDENTIFIER'  => '/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*/',
        'T_SYMBOL'      => '/^([{}();,:=+<>\#!?\[\]\.$*\/%|-])/',
    ];

    public function __construct(string $code)
    {
        $this->code = str_replace(["\r\n", "\r"], "\n", $code);
    }

    public function tokenize(): array
    {
        $tokens = [];
        $length = strlen($this->code);

        while ($this->cursor < $length) {
            $snippet = substr($this->code, $this->cursor);
            $match = false;

            foreach (self::PATTERNS as $type => $pattern) {
                if (preg_match($pattern, $snippet, $matches)) {
                    $value = $matches[0];

                    $column = $this->cursor - $this->lineStartOffset + 1;

                    if ($type !== 'T_WHITESPACE') {
                        $tokens[] = [
                            'type'   => $type,
                            'value'  => $value,
                            'line'   => $this->line,
                            'column' => $column
                        ];
                    }

                    $lastNewlinePos = strrpos($value, "\n");
                    if ($lastNewlinePos !== false) {
                        $this->line += substr_count($value, "\n");
                        $this->lineStartOffset = $this->cursor + $lastNewlinePos + 1;
                    }

                    $this->cursor += strlen($value);
                    $match = true;
                    break;
                }
            }

            if (!$match) {
                $this->cursor++;
            }
        }
        return $tokens;
    }
}
