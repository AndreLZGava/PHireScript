<?php

namespace PHPScript\Compiler\Processors;

class FunctionBodyProcessor implements PreprocessorInterface {

    public function process(string $code): string {
        // 1. Encontra funções marcadas para validação de array
        // Captura 1: O tipo interno (ex: User|Null)
        // Captura 2: O conteúdo dentro das chaves { ... }
        $pattern = '/\/\* @PS_VALIDATE_ARRAY\[(.+?)\] \*\/ \s*\{(.*?)\}/s';

        return preg_replace_callback($pattern, function($matches) {
            $rawTypes = $matches[1];
            $body = $matches[2];

            // 2. Criamos o código PHP de validação baseado nos tipos
            $validationCode = $this->buildValidationLogic($rawTypes);

            // 3. Substituímos os retornos dentro desse corpo específico
            // Usamos uma regex para pegar o 'return ...;'
            $newBody = preg_replace_callback('/return\s+(.+?);/', function($retMatches) use ($validationCode) {
                $returnValue = $retMatches[1];

                // Transformamos: return $x;
                // Em: $__tmp = $x; [Validação]; return $__tmp;
                return "{ \$_onArrayValidate = $returnValue; $validationCode return \$_onArrayValidate; }";
            }, $body);

            return "{ $newBody }";
        }, $code);
    }

    private function buildValidationLogic(string $rawTypes): string {
        $types = explode('|', $rawTypes);
        $conditions = [];

        foreach ($types as $type) {
            $conditions[] = $this->getCheckForType(trim($type), '$item');
        }

        $check = implode(' || ', $conditions);

        // O código gerado usa a constante PS_STRICT_MODE para performance
        return '
        if (defined("PS_STRICT_MODE") && PS_STRICT_MODE) {
            foreach ($_onArrayValidate as $item) {
                if (!(' . $check . ')) {
                    throw new \TypeError("PHPScript Error: Return element must be of type [' . $rawTypes . ']");
                }
            }
        }';
    }

    private function getCheckForType(string $type, string $varName): string {
        return match($type) {
            'Int'    => "is_int($varName)",
            'String' => "is_string($varName)",
            'Float'  => "is_float($varName)",
            'Bool'   => "is_bool($varName)",
            'Null'   => "is_null($varName)",
            'Ipv4'   => "filter_var($varName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)",
            'Ipv6'   => "filter_var($varName, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)",
            'Email'  => "filter_var($varName, FILTER_VALIDATE_EMAIL)",
            default  => "$varName instanceof $type",
        };
    }
}
