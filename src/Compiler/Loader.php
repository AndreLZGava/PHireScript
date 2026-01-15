<?php

declare(strict_types=1);

namespace PHPScript\Compiler;

use Exception;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use PHPScript\Runtime\Types\MetaTypes;
use PHPScript\Runtime\Types\SuperTypes;
use ReflectionClass;
use RuntimeException;

class Loader
{
    public function loadAndCompile($sourceDir, $distDir, $transpiler)
    {

        $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);

        foreach ($iterator as $file) {
            if ($file->getExtension() === 'ps') {
                $relativePath = substr($file->getPathname(), strlen($sourceDir) + 1);

                $outputFile = $distDir . '/' . str_replace('.ps', '.php', $relativePath);

                $outputSubDir = dirname($outputFile);
                if (!is_dir($outputSubDir)) {
                    mkdir($outputSubDir, 0755, true);
                }

                $this->compileFile($file->getPathname(), $outputFile, $transpiler);
            }
        }
    }

    public function load($sourceDir, $transpiler): array
    {
        $directory = new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS);
        $iterator = new RecursiveIteratorIterator($directory);
        $result = [];
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'ps') {
                try {
                    $sourceCode = file_get_contents($file->getPathname());
                    $result[] = $transpiler->compile($sourceCode, $file->getPathname());
                } catch (Exception $e) {
                    echo "\033[1;31m✗ Error in {$file->getPathname()}: " . $e->getMessage() . "\033[0m\n";
                    $this->getErrorInterface($e, $transpiler, $sourceCode);
                }
            }
        }
        return $result;
    }


    private function compileFile($input, $output, $transpiler)
    {
        try {
            $sourceCode = file_get_contents($input);
            $result = $transpiler->compile($sourceCode, $input);

            file_put_contents($output, $result);

            $output_text = [];
            $return_var = 0;
            exec("php -l " . escapeshellarg($output), $output_text, $return_var);

            if ($return_var !== 0) {
                echo "✗ Syntax Error in generated file $output:\n";
                echo implode("\n", $output_text) . "\n";
            } else {
                echo "\n\033[1;32m✔ $input -> $output\033[0m\n";
            }
        } catch (Exception $e) {
            echo "\033[1;31m✗ Error in $input: " . $e->getMessage() . "\033[0m\n";
            getErrorInterface($e, $transpiler, $sourceCode);
        }
    }


    public function getConfigFile()
    {
        $configs = json_decode(file_get_contents('PHPScript.json'), true);
        $configs['metatypes'] = $this->listClassesExtending(
            __DIR__ . '/../../src/Runtime/Types/MetaTypes/',
            MetaTypes::class
        );
        $configs['supertypes'] = $this->listClassesExtending(
            __DIR__ . '/../../src/Runtime/Types/SuperTypes/',
            SuperTypes::class
        );

        return $configs;
    }

    private function getErrorInterface($e, $transpiler, $code)
    {
        $maxLineWidth = 140;
        $red    = "\033[1;31m";
        $blue   = "\033[1;34m";
        $cyan   = "\033[1;36m";
        $gray   = "\033[0;90m";
        $yellow = "\033[1;33m";
        $reset  = "\033[0m";

        $originalLines = explode("\n", rtrim($code));
        $preParserLines = explode("\n", rtrim(
            $transpiler->getCodeBeforeGenerator()
        ));
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

    private function listClassesExtending(
        string $directory,
        string $baseClass
    ): array {
        $directory = realpath($directory);

        if ($directory === false || !is_dir($directory)) {
            throw new RuntimeException("Invalid path: {$directory}");
        }

        if (!class_exists($baseClass)) {
            throw new RuntimeException("Base class does not exists: {$baseClass}");
        }

        $classes = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $directory,
                FilesystemIterator::SKIP_DOTS
            )
        );
        foreach ($iterator as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = file_get_contents($file->getPathname());
            $tokens  = token_get_all($content);

            $namespace = '';
            $class     = null;
            $extends   = null;


            for ($i = 0; $i < count($tokens); $i++) {
                if ($tokens[$i][0] === T_NAMESPACE) {
                    $namespace = '';
                    for ($j = $i + 2; isset($tokens[$j]); $j++) {
                        if ($tokens[$j] === ';') {
                            break;
                        }
                        $namespace .= $tokens[$j][1];
                    }
                }

                if ($tokens[$i][0] === T_CLASS) {
                    $class = $tokens[$i + 2][1] ?? null;

                    for ($j = $i; isset($tokens[$j]); $j++) {
                        if (is_array($tokens[$j]) && $tokens[$j][0] === T_EXTENDS) {
                            $extends = '';
                            for ($k = $j + 2; isset($tokens[$k]); $k++) {
                                if (in_array($tokens[$k][0], [T_STRING, T_NS_SEPARATOR])) {
                                    $extends .= $tokens[$k][1];
                                } else {
                                    break;
                                }
                            }
                            break;
                        }
                    }
                    break;
                }
            }
            if (!$class) {
                continue;
            }

            $fqcn = $namespace ? "$namespace\\$class" : $class;

            if (!class_exists($fqcn)) {
                require_once $file->getPathname();
            }

            if (!class_exists($fqcn)) {
                continue;
            }

            $ref = new ReflectionClass($fqcn);

            if ($ref->isSubclassOf($baseClass)) {
                $classes[] = $fqcn;
            }
        }

        return $classes;
    }
}
