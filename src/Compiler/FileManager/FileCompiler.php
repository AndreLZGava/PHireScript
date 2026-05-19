<?php

declare(strict_types=1);

namespace PHireScript\Compiler\FileManager;

use Exception;
use PHireScript\Cache\CacheManager;
use PHireScript\Core\CompilerContext;
use PHireScript\Helper\Messenger;
use PHireScript\Runtime\RuntimeClass;

class FileCompiler
{
    public function __construct(
        private readonly CompilerContext $context,
        private readonly ErrorRenderer $errorRenderer,
        private readonly ?CacheManager $cache = null,
    ) {
    }

    public function compileFile(string $input, string $output, mixed $transpiler): void
    {
        $sourceCode = '';

        try {
            $startTime    = microtime(true);
            $tokensCached = $this->cache?->isFileValid($input) ?? false;
            $sourceCode   = (string) file_get_contents($input);
            $result       = $transpiler->compile($sourceCode, $input);

            if ($this->context->shouldPersist()) {
                $outputSubDir = dirname((string) $output);

                if (!is_dir($outputSubDir)) {
                    mkdir($outputSubDir, 0755, true);
                }

                file_put_contents($output, $result);

                if ($this->context->persistSnapshot()) {
                    $preParserCode    = $transpiler->getCodeBeforeGenerator();
                    $preCompiledCode  = \str_replace(
                        '.' . RuntimeClass::DEFAULT_FILE_EXTENSION,
                        '.' . RuntimeClass::DEFAULT_FILE_SNAPSHOT_EXTENSION,
                        $input
                    );
                    file_put_contents($preCompiledCode, $preParserCode);
                    Messenger::success("$input → $preCompiledCode", true);
                }

                $elapsedMs = (int) round((microtime(true) - $startTime) * 1000);

                if (!$tokensCached) {
                    $output_text = [];
                    $return_var  = 0;
                    exec("php -l " . escapeshellarg((string) $output), $output_text, $return_var);

                    if ($return_var !== 0) {
                        Messenger::error("Syntax Error in generated file $output:", true);
                        Messenger::text(\implode("\n", $output_text));
                        return;
                    }
                }

                Messenger::success("$input → $output  [{$elapsedMs}ms]", true);
            }

            if ($this->context->inMemory && !$this->context->displayInsideCompiler) {
                Messenger::success("SUCCESSFUL PHP OUTPUT", true);
                Messenger::text($result);
            }

            if ($this->context->inMemory && $this->context->displayInsideCompiler) {
                $this->errorRenderer->renderExecution($sourceCode, $result);
            }
        } catch (Exception $e) {
            if ($this->context->displayInsideCompiler) {
                $this->errorRenderer->renderWeb($e, $transpiler, $sourceCode);
            }

            Messenger::error("Error in $input: " . $e->getMessage(), true);
            $this->errorRenderer->renderCli($e, $transpiler, $sourceCode);
        }
    }

    public function copyFile(string $input, string $output): void
    {
        $outputDir = dirname($output);

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        copy($input, $output);

        Messenger::info("[Copied]: {$input} → {$output}");
    }
}
