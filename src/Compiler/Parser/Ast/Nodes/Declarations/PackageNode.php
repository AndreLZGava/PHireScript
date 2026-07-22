<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Ast\Nodes\Declarations;

use Exception;
use PHireScript\Compiler\Parser\Managers\Token\Token;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Runtime\Exceptions\CompileException;
use PHireScript\Runtime\RuntimeClass;
use PHireScript\Compiler\Parser\Ast\Nodes\Statement;

class PackageNode extends Statement
{
    public string $namespace;
    public string $completeObjectReference;

    public string $completePackage;

    public function __construct(
        public Token $token,
        public string $file,
        public ?string $package = null,
        public ?string $object = null,
    ) {
        //$this->validate();
    }

    public function validate()
    {
        $basename = basename($this->file);
        $ext = RuntimeClass::DEFAULT_FILE_EXTENSION;
        $textExt = RuntimeClass::DEFAULT_FILE_TEST_EXTENSION;

        if (is_null($this->package)) {
            throw new CompileException(
                'Package must be defined!',
                $this->token->line,
                $this->token->column,
            );
        }
        if (
            !str_starts_with($basename, (string) $this->object) &&
            (
                \str_ends_with($basename, '.' . $ext) ||
                \str_ends_with($basename, '.' . $textExt)
            )
        ) {
            $message = \str_ends_with($basename, '.' . $ext) ?
                'File name must match class/interface/type/' .
                'immutable/trait name! File ' . $this->file .
                ' object name ' . $this->object :
                'File name must match class/validate ' .
                'name! File ' . $this->file . ' object name ' . $this->object;
            throw new CompileException(
                $message,
                $this->token->line,
                $this->token->column,
            );
        }
    }

    public function generateNamespace(ParseContext $context): void
    {
        $config = $context->contextManager->getConfig();

        // Extract the directory part of the file path (everything before /{objectName})
        $fileDir = \current(\explode('/' . $this->object, $this->file));

        // Build the namespace segment from cwd-relative path so that:
        //   cwd = /project, file = /project/src/output/Foo.phs → segment = src\output
        //   cwd = /project, file = /project/samples/case_1/Foo.phs → segment = samples\case_1
        $cwd = \rtrim((string) \getcwd(), '/');

        if (\str_starts_with($fileDir, $cwd . '/')) {
            $relative = \substr($fileDir, \strlen($cwd) + 1);
        } else {
            $relative = '';
        }

        $relative = \str_replace('/', '\\', $relative);

        $this->completePackage = $this->package . '.' . $this->object;
        $this->namespace = !empty($relative)
            ? $config['namespace'] . '\\' . $relative
            : (string) $config['namespace'];
        $this->completeObjectReference = '\\' . $this->namespace . '\\' . $this->object . '::class';
        $context->setCurrentPackage($this->completePackage);
    }
}
