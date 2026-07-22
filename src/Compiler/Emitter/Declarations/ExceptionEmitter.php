<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\Declarations;

use PHireScript\Compiler\Emitter\Base\EmitContext;
use PHireScript\Compiler\Emitter\Base\NodeEmitter;
use PHireScript\Compiler\Emitter\Base\NodeEmitterAbstract;
use PHireScript\Compiler\Parser\Ast\Nodes\Declarations\ExceptionNode;
use PHireScript\Compiler\Parser\Ast\Nodes\OOP\PropertyNode;
use PHireScript\Helper\TypeResolver;

class ExceptionEmitter extends NodeEmitterAbstract implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof ExceptionNode;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        // User-defined parent already imported via `use` — no leading backslash.
        // PHP built-in default (Exception) needs the global namespace prefix.
        $userDefined = $node->extends !== null;
        $parent = $userDefined ? $node->extends->child : 'Exception';
        $extendsStr = $userDefined ? "extends {$parent}" : "extends \\{$parent}";

        // Bare exception — no body
        if (empty($node->properties) && !$node->hasCustomConstructor && $node->messageTemplate === null) {
            return "class {$node->name} {$extendsStr}\n{\n}\n";
        }

        $code = "class {$node->name} {$extendsStr}\n{\n";
        $code .= $this->emitConstructor($node, $ctx);
        $code .= "}\n";

        return $code;
    }

    private function emitConstructor(ExceptionNode $node, EmitContext $ctx): string
    {
        if ($node->hasCustomConstructor) {
            return '';
        }

        $params = [];
        foreach ($node->properties as $prop) {
            $phpType = $this->resolvePhpType($prop, $ctx);
            $params[] = "        public readonly {$phpType} \${$prop->name}";
        }

        // Standard exception params
        $params[] = '        string $message = \'\'';
        $params[] = '        int $code = 0';
        $params[] = '        ?\Throwable $previous = null';
        $params[] = '        public readonly array $context = []';

        $paramStr = \implode(",\n", $params);

        if ($node->messageTemplate !== null) {
            $ctorBody = "        if (\$message === '') {\n"
                . $this->buildSprintfLine($node->messageTemplate, $node->properties)
                . "        }\n"
                . "        parent::__construct(\$message, \$code, \$previous);\n";
        } else {
            $ctorBody = "        parent::__construct(\$message, \$code, \$previous);\n";
        }

        return "    public function __construct(\n{$paramStr}\n    ) {\n{$ctorBody}    }\n";
    }

    private function resolvePhpType(PropertyNode $prop, EmitContext $ctx): string
    {
        if (!empty($prop->resolvedTypeInfo)) {
            return $ctx->types->phpType($prop);
        }
        // Fallback: map PHireScript primitive names directly
        $raw = $prop->types[0] ?? 'mixed';
        return TypeResolver::isPrimitive($raw) ? TypeResolver::nativeType($raw) : $raw;
    }

    private function buildSprintfLine(string $template, array $properties): string
    {
        // Replace {propertyName} with %s and collect property names in order
        $args = [];
        $phpTemplate = \preg_replace_callback('/\{(\w+)\}/', function ($m) use (&$args) {
            $args[] = '$' . $m[1];
            return '%s';
        }, $template);

        if (empty($args)) {
            return "            \$message = " . \var_export($template, true) . ";\n";
        }

        $argsStr = \implode(', ', $args);
        return "            \$message = sprintf(" . \var_export($phpTemplate, true) . ", {$argsStr});\n";
    }
}
