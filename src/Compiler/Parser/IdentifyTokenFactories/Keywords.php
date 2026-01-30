<?php

declare(strict_types=1);

namespace PHireScript\Compiler\Parser\IdentifyTokenFactories;

use PHireScript\Compiler\Parser\Ast\Node;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\AbstractKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\AsKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\CacheKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ClassKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ExtendsKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ExternalKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Immutable;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ImplementsKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\InjectKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\InterfaceKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\PkgKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ReturnKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ScheduleKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\ScopedKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\SingletonKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\TraitKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\TransientKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\Type;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\UseKey;
use PHireScript\Compiler\Parser\IdentifyTokenFactories\Keywords\WithKey;
use PHireScript\Compiler\Parser\ParseContext;
use PHireScript\Compiler\Program;
use PHireScript\Helper\Debug\Debug;

class Keywords extends GlobalFactory
{
    private array $factories;

    public function process(Token $token, ParseContext $parseContext): ?Node
    {
        $this->factories = [
            'type' => Type::class,
            'immutable' => Immutable::class,
            'interface' => InterfaceKey::class,
            'class' => ClassKey::class,
            'return' => ReturnKey::class,
            'pkg' => PkgKey::class,
            'use' => UseKey::class,
            'external' => ExternalKey::class,
            'trait' => TraitKey::class,
            'abstract' => AbstractKey::class,
            'extends' => ExtendsKey::class,
            'implements' => ImplementsKey::class,
            'with' => WithKey::class,

            // Support to class anotation
            'inject' => InjectKey::class,
            'cache' => CacheKey::class,
            'singleton' => SingletonKey::class,
            'transient' => TransientKey::class,
            'scoped' => ScopedKey::class,
            'schedule' => ScheduleKey::class,
            'as' => AsKey::class
        ];

        $tokenValue = $this->tokenManager->getCurrentToken()->value;
        if (!isset($this->factories[$tokenValue])) {
            Debug::show($tokenValue);
        }

        $class = $this->factories[$tokenValue] ?? General::class;
        $processor = new $class($this->tokenManager, $this->parseContext);

        return $processor->process($program);
    }
}
