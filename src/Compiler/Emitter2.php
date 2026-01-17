<?php

declare(strict_types=1);

namespace PHireScript\Compiler;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Parser\Ast\AssignmentNode;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Parser\Ast\GlobalStatement;
use PHireScript\Compiler\Parser\Ast\MethodDefinition;
use PHireScript\Compiler\Parser\Ast\PackageStatement;
use PHireScript\Compiler\Parser\Ast\PropertyAccessNode;
use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\ThisExpressionNode;
use PHireScript\Compiler\Parser\Ast\VariableNode;
use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\RuntimeClass;

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
            /**
             * @todo implement use cases
            foreach (array_unique($this->uses) as $use) {
                 $classesCode .= "use $use;\n";
            }
             */

            if ($node instanceof PackageStatement) {
                $classesCode .= $this->emitPackage($node);
            }

            if ($node instanceof ClassDefinition && $node->type === 'interface') {
                $classesCode .= $this->emitInterface($node);
                continue;
            }
            if (
                $node instanceof ClassDefinition &&
                isset($node->type) &&
                in_array($node?->type, RuntimeClass::OBJECT_AS_CLASS, true)
            ) {
                $classesCode .= $this->emitClass($node);
            }
        }
        $code = "<?php\n\n";

        return $code . "\n" . $classesCode;
    }

    private function emitPackage(PackageStatement $node)
    {
        return "namespace " . $node->namespace . ";\n\n";
    }

    protected function emitClass(ClassDefinition $class): string
    {
        $name = $class->name;
        $code = $class->readOnly ? 'readonly ' : '';
        $code .= "class $name {\n";

        foreach ($class->body as $member) {
            if ($member instanceof GlobalStatement) {
                $code .= $this->emitComment($member);
            }

            if ($member instanceof PropertyDefinition) {
                $code .= $this->emitProperty($member);
            }
        }

        $code .= "\n" . $this->emitConstructor($class);

        foreach ($class->body as $member) {
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

        $returnType = !empty($returnTypeRaw) ? ": " .
            strtolower($returnTypeRaw) : "";

        if ($isInterface) {
            return "    $modifiers function {$method->name}($argsList)$returnType;\n";
        }

        $bodyLines = "";
        foreach ($method->bodyCode as $node) {
            $bodyLines .= "        " . $this->emitNode($node, $method->returnType) . "\n";
        }

        return "    $modifiers function {$method->name}($argsList)$returnType {\n" .
            $bodyLines .
            "    }\n\n";
    }


    protected function emitNode($node, $returnType = null): string
    {
        return match (true) {
            $node instanceof \PHireScript\Compiler\Parser\Ast\ReturnNode =>
            $this->emitReturn($node, $returnType),

            $node instanceof \PHireScript\Compiler\Parser\Ast\AssignmentNode =>
            $this->emitAssignment($node),

            $node instanceof \PHireScript\Compiler\Parser\Ast\GlobalStatement =>
            trim($node->code),

            default => "// Unknown Node: " . $node::class
        };
    }

    protected function emitAssignment(AssignmentNode $node): string
    {
        $left = $this->emitPropertyAssignment($node->left);
        $right = $this->emitPropertyAssignment($node->right);

        return "{$left} = {$right};";
    }

    public function emitPropertyAssignment($node): string
    {
        if ($node instanceof ThisExpressionNode) {
            return '$this';
        }

        if ($node instanceof VariableNode) {
            return '$' . $node->name;
        }

        if ($node instanceof PropertyAccessNode) {
            $object = $this->emitPropertyAssignment($node->object);

            $property = is_string($node->property)
                ? $node->property
                : $node->property->name;

            return "{$object}->{$property}";
        }

        return '';
    }

    protected function emitReturn(\PHireScript\Compiler\Parser\Ast\ReturnNode $node, $returnType): string
    {
        //Debug::show($node);exit;
        $expression = $node->expression ? $this->emitExpression($node->expression) : "";

        $isTypedArray = is_string($returnType) && str_starts_with($returnType, '[');

        if (!$this->config['dev'] || !$isTypedArray || empty($expression)) {
            return "return $expression;";
        }

        $this->uses[] = \PHireScript\Runtime\Types\TypeGuard::class;

        $innerTypes = trim($returnType, '[]');
        $typesArray = "['" . implode("', '", explode('|', $innerTypes)) . "']";

        return "return TypeGuard::validateArray($expression, $typesArray);";
    }

    protected function emitExpression($expr): string
    {
        if ($expr instanceof \PHireScript\Compiler\Parser\Ast\LiteralNode) {
            return ($expr->rawType === 'String') ? "{$expr->value}" : $expr->value;
        }

        if ($expr instanceof \PHireScript\Compiler\Parser\Ast\PropertyAccessNode) {
            $expression = '';
            if ($expr->object instanceof ThisExpressionNode) {
                $expression = '$this';
            }
            return "{$expression}->{$expr->property}";
        }

        if ($expr instanceof \PHireScript\Compiler\Parser\Ast\ArrayLiteralNode) {
            $elements = [];
            foreach ($expr->elements as $el) {
                $elements[] = $this->emitExpression($el);
            }
            return "[" . implode(', ', $elements) . "]";
        }

        if ($expr instanceof \PHireScript\Compiler\Parser\Ast\VoidExpressionNode) {
            return "";
        }

        return "// Unknown Node: " . $expr::class;
    }

    protected function emitProperty(PropertyDefinition $prop): string
    {
        $modifier = $this->joinAllModifiers($prop->modifiers) ?? 'public';
        $phpType = $this->getPhpType($prop);

        return "    $modifier $phpType \${$prop->name};\n";
    }

    private function getDefaultValue(PropertyDefinition $prop): string
    {
        if (empty($prop->defaultValue)) {
            return '';
        }
        $propertyValue = $prop->defaultValue === 'Null' ? 'null' : $prop->defaultValue;
        $propertyValue = $propertyValue === 'True' ? 'true' : $propertyValue;
        $propertyValue = $propertyValue === 'False' ? 'false' : $propertyValue;
        return ' = ' . $propertyValue;
    }

    protected function emitConstructor(ClassDefinition $class): string
    {
        $params = [];
        $assignments = [];

        foreach ($class->body as $member) {
            if ($member instanceof PropertyDefinition) {
                $phpType = $this->getPhpType($member);
                $defaultValue = $this->getDefaultValue($member);
                $params[] = "$phpType \${$member->name}{$defaultValue}";

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
        $explicitTypes =  explode('|', $this->getPhpType($prop));
        $itemsToVerify = in_array('null', $explicitTypes, true) ?
            count($types) - 1 :
            count($types);
        $var = $prop->name;
        if ($itemsToVerify > 1) {
            $this->uses[] = \PHireScript\Runtime\Types\UnionType::class;

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
        $typeInfos = $prop->resolvedTypeInfo;
        $types = [];
        foreach ($typeInfos as $typeInfo) {
            if ($typeInfo['category'] === 'supertype') {
                $this->uses[] = $typeInfo['class'];
                $types[] = "string";
            }

            if ($typeInfo['category'] === 'metatype' || $typeInfo['category'] === 'custom') {
                $this->uses[] = $typeInfo['class'] ?? $typeInfo['name'];
                $types[] = $prop->type;
            }

            if ($typeInfo['category'] === 'primitive') {
                $types[] = $typeInfo['native'];
            }

            if (isset($typeInfo['name']) && $typeInfo['name'] === 'Null') {
                $types[] = 'null';
            }
        }

        return implode('|', array_unique($types));
    }
}
