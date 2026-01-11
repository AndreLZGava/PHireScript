<?php

namespace PHPScript\Compiler;

use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\MethodDefinition;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Helper\Debug\Debug;

// responsible to emmit a valid PHP code
// Possbly can use Processors from PHPScript\Compiler\Processors

class Emitter
{
    private array $uses = [];

    public function __construct(private array $config)
    {
    }

    public function emit(Program $ast): string
    {
        $classesCode = "";
        foreach ($ast->statements as $node) {
            if ($node instanceof GlobalStatement) {
                $classesCode .= $this->emitComment($node);
            }

            if ($node instanceof ClassDefinition && $node?->type === 'class' || $node?->type === 'type') {
                $classesCode .= $this->emitClass($node);
            }

            if ($node instanceof ClassDefinition && $node->type === 'interface') {
                $classesCode .= $this->emitInterface($node);
            }
        }

        $code = "<?php\n\n";
        $code .= "namespace " . $this->config['namespace'] . ";\n\n";

        foreach (array_unique($this->uses) as $use) {
            $code .= "use $use;\n";
        }

        return $code . "\n" . $classesCode;
    }

    protected function emitClass(ClassDefinition $class): string
    {
        $name = $class->name;
        $code = "class $name {\n";

        $code .= "\n" . $this->emitConstructor($class);

        foreach ($class->body as $member) {
            if ($member instanceof GlobalStatement) {
                $code .= $this->emitComment($member);
            }

            if ($member instanceof PropertyDefinition) {
                $code .= $this->emitProperty($member);
            }

            if ($member instanceof MethodDefinition) {
                $code .= $this->emitMethod($member, false);
            }
        }

        $code .= "}\n";
        return $code;
    }

    protected function emitInterface(ClassDefinition $interface): string
    {
        $name = $interface->name;
        $code = "interface $name {\n";

        foreach ($interface->body as $member) {
            if ($member instanceof GlobalStatement) {
                $code .= $this->emitComment($member);
            }

            if ($member instanceof PropertyDefinition) {
                $code .= $this->emitProperty($member);
            }

            if ($member instanceof MethodDefinition) {
                $code .= $this->emitMethod($member, true);
            }
        }


        $code .= "}\n";
        return $code;
    }

    protected function emitComment(GlobalStatement $stmt): string
    {
        return "    " . trim($stmt->code) . "\n";
    }

    //@todo one moment we will need to sort it before joining
    private function joinAllModifiers(array $modifiers): string
    {
        return $modifiers ? implode(' ', $modifiers) : null;
    }

    protected function emitMethod(MethodDefinition $method, bool $isInterface = false): string
    {
        $modifiers = $this->joinAllModifiers($method->modifiers) ?? 'public';

        $args = [];
        foreach ($method->args as $arg) {
            if ($arg instanceof PropertyDefinition) {
                $type = $this->getPhpType($arg);
                $args[] = "$type \${$arg->name}";
            }
        }
        $argsList = implode(', ', $args);

        $returnTypeRaw = $method->returnType;

        if (is_array($returnTypeRaw)) {
            $returnTypeRaw = 'array';
        } elseif (str_starts_with((string)$returnTypeRaw, '[')) {
            $returnTypeRaw = 'array';
        }

        $returnType = !empty($returnTypeRaw) ? ": " . strtolower($returnTypeRaw) : "";

        if ($isInterface) {
            return "    $modifiers function {$method->name}($argsList)$returnType;\n";
        }

        $bodyLines = "";
        foreach ($method->bodyCode as $node) {
            $bodyLines .= "        " . $this->emitNode($node) . "\n";
        }

        return "    $modifiers function {$method->name}($argsList)$returnType {\n" .
            $bodyLines .
            "    }\n";
    }

    protected function emitNode($node): string
    {
        return match (true) {
            $node instanceof \PHPScript\Compiler\Parser\Ast\ReturnNode =>
            "return " . ($node->expression ? $this->emitExpression($node->expression) : "") . ";",

            $node instanceof \PHPScript\Compiler\Parser\Ast\GlobalStatement =>
            trim($node->code),

            default => "// Unknown Node: " . get_class($node)
        };
    }

    protected function emitExpression($expr): string
    {
        if ($expr instanceof \PHPScript\Compiler\Parser\Ast\LiteralNode) {
            return ($expr->rawType === 'String') ? "{$expr->value}" : $expr->value;
        }

        if ($expr instanceof \PHPScript\Compiler\Parser\Ast\ArrayLiteralNode) {
            $elements = [];
            foreach ($expr->elements as $el) {
                $elements[] = $this->emitExpression($el);
            }
            return "[" . implode(', ', $elements) . "]";
        }

        return "";
    }

    protected function emitProperty(PropertyDefinition $prop): string
    {
        $modifier = $this->joinAllModifiers($prop->modifiers) ?? 'public';
        $phpType = $this->getPhpType($prop);

        return "    $modifier $phpType \${$prop->name};\n";
    }

    protected function emitConstructor(ClassDefinition $class): string
    {
        $params = [];
        $assignments = [];

        foreach ($class->body as $member) {
            if ($member instanceof PropertyDefinition) {
                $phpType = $this->getPhpType($member);
                $params[] = "$phpType \${$member->name}";

                $assignments[] = $this->generateAssignmentLine($member);
            }
        }

        if (!$assignments) {
            return '';
        }

        $code = "    public function __construct(\n        " . implode(",\n        ", $params) . "\n    ) {\n";
        $code .= "        " . implode("\n        ", $assignments) . "\n    }\n";

        return $code;
    }

    private function generateAssignmentLine(PropertyDefinition $prop): string
    {
        $types = $prop->resolvedTypeInfo;
        $var = $prop->name;

        if (count($types) > 1) {
            $this->uses[] = "PHPScript\\Runtime\\Types\\UnionType";

            $typeClasses = [];
            foreach ($types as $t) {
                if (isset($t['class'])) {
                    $this->uses[] = $t['class'];
                    $className = (new \ReflectionClass($t['class']))->getShortName();
                    $typeClasses[] = "$className::class";
                }
            }

            $classList = implode(', ', $typeClasses);
            return "\$this->$var = UnionType::cast(\$$var, [$classList]);";
        }

        $typeInfo = $types[0];
        return match ($typeInfo['category']) {
            'supertype' => "\$this->$var = {$prop->type}::cast(\$$var);",
            'metatype'  => "\$this->$var = \$$var instanceof {$prop->type} ? \$$var : new {$prop->type}(\$$var);",
            default     => "\$this->$var = \$$var;"
        };
    }

    private function getPhpType(PropertyDefinition $prop): string
    {
        $typeInfo = $prop->resolvedTypeInfo[0];

        if ($typeInfo['category'] === 'supertype') {
            $this->uses[] = $typeInfo['class'];
            return "string";
        }

        if ($typeInfo['category'] === 'metatype' || $typeInfo['category'] === 'custom') {
            $this->uses[] = $typeInfo['class'] ?? $typeInfo['name'];
            return $prop->type;
        }

        return $typeInfo['native'] ?? 'mixed';
    }
}
