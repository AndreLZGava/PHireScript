<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class MapMethods extends GeneralType {
  public function __construct(
    public array $types = [],
  ) {
  }

  public function contains() {
    return new BaseMethods(
      name: 'contains?',
      phpCodeForConversion: 'in_array(@searching, @self)',
      returnOfPhpExecution: ['Bool'],
      subTypes: $this->types,
      params: [
        new BaseParams(name: '@searching', type: 'mixed', required: true)
      ]
    );
  }

  public function keyDefined() {
    return new BaseMethods(
      name: 'keyDefined?',
      phpCodeForConversion: 'array_key_exists(@searching, @self)',
      returnOfPhpExecution: ['Bool'],
      subTypes: $this->types,
      params: [
        new BaseParams(name: '@searching', type: 'string', required: true)
      ]
    );
  }

  public function add() {
    return new BaseMethods(
      name: 'add!',
      phpCodeForConversion: '@self[@key] = @value',
      returnOfPhpExecution: [],
      subTypes: [],
      params: [
        new BaseParams(name: '@value', type: 'mixed', required: true),
        new BaseParams(name: '@key', type: 'string', required: true, relatedKeyParam: true),
      ]
    );
  }

  public function last() {
    return new BaseMethods(
      name: 'last',
      phpCodeForConversion: 'empty(@self) ? null : @self[array_key_last(@self)];',
      returnOfPhpExecution: ['Mixed'],
      subTypes: $this->types,
      params: [],
    );
  }

  public function first() {
    return new BaseMethods(
      name: 'first',
      phpCodeForConversion: 'current(@self ?? [])',
      returnOfPhpExecution: ['Mixed'],
      subTypes: $this->types,
      params: [],
    );
  }
}
