<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\Managers;

use PHireScript\Compiler\Parser\Ast\PropertyDefinition;
use PHireScript\Compiler\Parser\Ast\VariableDeclarationNode;
use PHireScript\Compiler\Parser\Ast\VariableReferenceNode;
use PHireScript\Helper\Debug\Debug;

class SymbolTableManager
{
    private array $typeDefinitions = [];

    public function __construct()
    {
        $targetDir = __DIR__ . '/../../../../src/Runtime/DefaultOverrideMethods/Types';
        $getDefaultOverrideMethods = $this->scanAndBuildRegistry($targetDir);
        $this->typeDefinitions = $getDefaultOverrideMethods;
    }


    private function scanAndBuildRegistry(string $directory): array
    {
        if (!is_dir($directory)) {
            throw new \RuntimeException("Diretório não encontrado: $directory");
        }

        $registry = [];
        $files = glob($directory . '/*.php');

        foreach ($files as $file) {
          // 1. Identificar a classe contida no arquivo
          // Usamos get_declared_classes() antes e depois do require para descobrir qual classe foi carregada
            $classesBefore = get_declared_classes();
            require_once $file;
            $classesAfter = get_declared_classes();
            $newClasses = array_diff($classesAfter, $classesBefore);

            foreach ($newClasses as $className) {
                $reflector = new \ReflectionClass($className);

              // Pular classes abstratas ou interfaces
                if (!$reflector->isInstantiable()) {
                    continue;
                }

              // 2. Instanciar a classe (Resolvendo dependências do __construct automaticamente)
                try {
                    $instance = $this->resolveAndInstantiate($reflector);
                } catch (\Throwable $e) {
                  // Se não conseguir instanciar, pula essa classe e loga erro se necessário
                    error_log("Não foi possível instanciar {$className}: " . $e->getMessage());
                    continue;
                }

              // Nome curto da classe (ex: Queue) para a chave do array
                $shortName = $reflector->getShortName();
                $registry[$shortName] = [];

              // 3. Ler métodos públicos
                $methods = $reflector->getMethods(\ReflectionMethod::IS_PUBLIC);

                foreach ($methods as $method) {
                    $methodName = $method->getName();

                  // Ignorar métodos mágicos (__construct, __destruct, etc)
                    if (str_starts_with($methodName, '__')) {
                        continue;
                    }

                  // 4. Executar o método para obter o retorno (BaseMethods)
                    try {
                      // Invoca o método na instância criada.
                      // Nota: Se o método exigir parâmetros obrigatórios sem default, isso falhará.
                      // No seu exemplo, ...$params é opcional, então funciona.
                        $result = $method->invoke($instance);

                        $registry[$shortName][$methodName] = $result;
                    } catch (\Throwable $e) {
                        error_log("Erro ao executar método {$className}::{$methodName}: " . $e->getMessage());
                    }
                }
            }
        }

        return $registry;
    }


    private function resolveAndInstantiate(\ReflectionClass $reflector): object
    {
        $constructor = $reflector->getConstructor();

        if (!$constructor || $constructor->getNumberOfParameters() === 0) {
            return $reflector->newInstance();
        }

        $args = [];
        foreach ($constructor->getParameters() as $param) {
            if ($param->isDefaultValueAvailable()) {
                $args[] = $param->getDefaultValue();
            } else {
                $type = $param->getType();
                if ($type instanceof \ReflectionNamedType) {
                    $typeName = $type->getName();
                    if ($typeName === 'array') {
                        $args[] = [];
                    } elseif ($typeName === 'int') {
                        $args[] = 0;
                    } elseif ($typeName === 'string') {
                        $args[] = '';
                    } elseif ($typeName === 'bool') {
                        $args[] = false;
                    } else {
                        $args[] = null;
                    }
                } else {
                    $args[] = null;
                }
            }
        }

        return $reflector->newInstanceArgs($args);
    }
}
