<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Emitter\NodeEmitters;

use PHireScript\Compiler\Emitter\EmitContext;
use PHireScript\Compiler\Emitter\NodeEmitter;
use PHireScript\Compiler\Parser\Ast\ClassDefinition;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class ProgramEmitter implements NodeEmitter
{
    public function supports(object $node, EmitContext $ctx): bool
    {
        return $node instanceof Program;
    }

    public function emit(object $node, EmitContext $ctx): string
    {
        //Debug::show($node->statements);exit;
        $code['init'] = "<?php\n\n";
        foreach ($node->statements as $position => $stmt) {
            $code[get_class($stmt) . '_' . $position] = $ctx->emitter->emit($stmt, $ctx);
        }
        $uses = $this->processUses($ctx);
        return $this->processEntireCode($code, $uses);
    }

    private function processEntireCode(array $code, string $uses)
    {
        $processedCode = "";

        foreach ($code as $key => $code) {
            if ($key === "PHireScript\Compiler\Parser\Ast\ClassDefinition") {
                $processedCode .= $uses . "\n";
            }
            $processedCode .= $code . "\n";
        }
        return $processedCode;
    }

    private function processUses(EmitContext $ctx)
    {
        $useStatements = "";
        $uses = $ctx->uses->getUses();
        if (!empty($uses)) {
            foreach ($uses as $class => $val) {
                $useStatements .= "use {$class};\n";
            }
            $useStatements .= "\n";
        }
        return $useStatements;
    }
}
