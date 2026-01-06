<?php

namespace PHPScript\Compiler;

use PHPScript\Compiler\Parser\Ast\ClassDefinition;
use PHPScript\Compiler\Parser\Ast\GlobalStatement;
use PHPScript\Compiler\Parser\Ast\PropertyDefinition;
use PHPScript\Helper\Debug\Debug;

// responsible to emmit a valid PHP code
// Possbly can use Processors from PHPScript\Compiler\Processors

class Emitter {
  private array $uses = [];

  public function emit(Program $ast): string {
    $classesCode = "";
    foreach ($ast->statements as $node) {

      if ($node instanceof GlobalStatement) {
        $classesCode .= $this->emitComment($node);
      }

      if ($node instanceof ClassDefinition) {
        $classesCode .= $this->emitClass($node);
      }
    }

    $code = "<?php\n\n";
    $code .= "namespace App\Generated;\n\n";

    foreach (array_unique($this->uses) as $use) {
      $code .= "use $use;\n";
    }

    return $code . "\n" . $classesCode;
  }

  protected function emitClass(ClassDefinition $class): string {
    $name = $class->name;
    $code = "class $name {\n";

    foreach ($class->body as $member) {
      if ($member instanceof GlobalStatement) {
        $code .= $this->emitComment($member);
      }

      if ($member instanceof PropertyDefinition) {
        $code .= $this->emitProperty($member);
      }
    }

    $code .= "\n" . $this->emitConstructor($class);

    $code .= "}\n";
    return $code;
  }

  protected function emitComment(GlobalStatement $stmt): string {
    return "    " . trim($stmt->code) . "\n";
  }

  protected function emitProperty(PropertyDefinition $prop): string {
    $modifier = $prop->modifiers[0] ?? 'public';
    $phpType = $this->getPhpType($prop);

    return "    $modifier $phpType \${$prop->name};\n";
  }

  protected function emitConstructor(ClassDefinition $class): string {
    $params = [];
    $assignments = [];

    foreach ($class->body as $member) {
      if ($member instanceof PropertyDefinition) {
        $phpType = $this->getPhpType($member);
        $params[] = "$phpType \${$member->name}";

        $assignments[] = $this->generateAssignmentLine($member);
      }
    }

    $code = "    public function __construct(\n        " . implode(",\n        ", $params) . "\n    ) {\n";
    $code .= "        " . implode("\n        ", $assignments) . "\n    }\n";

    return $code;
  }

  private function generateAssignmentLine(PropertyDefinition $prop): string {
    $types = $prop->resolvedTypeInfo;
    $var = $prop->name;

    // Se houver mais de um tipo, tratamos como Union Type
    if (count($types) > 1) {
      // Adicionamos o novo namespace ao cabeçalho de 'uses'
      $this->uses[] = "PHPScript\\Runtime\\Types\\UnionType";

      $typeClasses = [];
      foreach ($types as $t) {
        // Adicionamos a classe específica (Ipv4, Ipv6, etc) para o cast funcionar
        if (isset($t['class'])) {
          $this->uses[] = $t['class'];
          // Pegamos apenas o nome curto da classe para o array de cast
          $className = (new \ReflectionClass($t['class']))->getShortName();
          $typeClasses[] = "$className::class";
        }
      }

      $classList = implode(', ', $typeClasses);
      return "\$this->$var = UnionType::cast(\$$var, [$classList]);";
    }

    // Lógica para tipo único (mantida)
    $typeInfo = $types[0];
    return match ($typeInfo['category']) {
      'supertype' => "\$this->$var = {$prop->type}::cast(\$$var);",
      'metatype'  => "\$this->$var = \$$var instanceof {$prop->type} ? \$$var : new {$prop->type}(\$$var);",
      default     => "\$this->$var = \$$var;"
    };
  }

  private function getPhpType(PropertyDefinition $prop): string {
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
