<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class StringMethods extends GeneralType {
    public function length() {
        return new BaseMethods(
            'length',
            'strlen(@self)',
            ['Int'],
        );
    }

    public function toUpperCase() {
        return new BaseMethods(
            'toUpperCase',
            phpCodeForConversion: 'mb_strtoupper(@self, @format)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@format', 'string', false, 'UTF-8'),
            ],
        );
    }

    public function toLowerCase() {
        return new BaseMethods(
            'toLowerCase',
            phpCodeForConversion: 'mb_strtolower(@self, @format)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@format', 'string', false, 'UTF-8'),
            ],
        );
    }

    public function replace() {
        return new BaseMethods(
            'replace',
            phpCodeForConversion: 'str_replace(@from, @to, @self)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@from', 'string', true),
                new BaseParams('@to', 'string', true),
            ],
        );
    }

    public function removeSpaces() {
        return new BaseMethods(
            'removeSpaces',
            phpCodeForConversion: 'trim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeSpacesLeft() {
        return new BaseMethods(
            'removeSpacesLeft',
            phpCodeForConversion: 'ltrim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeSpacesRight() {
        return new BaseMethods(
            'removeSpacesRight',
            phpCodeForConversion: 'rtrim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeAllSpaces() {
        return new BaseMethods(
            'removeAllSpaces',
            phpCodeForConversion: "preg_replace('/\s+/', '', @self)",
            returnOfPhpExecution: ['String'],
        );
    }

    public function contains() {
        return new BaseMethods(
            'contains?',
            phpCodeForConversion: 'str_contains(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function endWith() {
        return new BaseMethods(
            'endWith?',
            phpCodeForConversion: 'str_ends_with(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function startWith() {
        return new BaseMethods(
            'startWith?',
            phpCodeForConversion: 'str_starts_with(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function decrement() {
        return new BaseMethods(
            'decrement',
            phpCodeForConversion: 'str_decrement(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function increment() {
        return new BaseMethods(
            'increment',
            phpCodeForConversion: 'str_increment(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function getCsv() {
        return new BaseMethods(
            'getCsv',
            phpCodeForConversion: 'str_getcsv(@self, @separator, @enclosure, @escape)',
            returnOfPhpExecution: ['Array'],
            params: [
                new BaseParams('@separator', 'string', false, ','),
                new BaseParams('@enclosure', 'string', false, "\""),
                new BaseParams('@escape', 'string', false, "\\"),
            ]
        );
    }

    public function join() {
        return new BaseMethods(
            'join',
            phpCodeForConversion: '@self . implode(\'\', [@params])',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@params', 'string', true),
            ]
        );
    }

    public function repeat() {
        return new BaseMethods(
            'repeat',
            phpCodeForConversion: 'str_repeat("@self, @times)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@times', 'int', true),
            ]
        );
    }

    public function shuffle() {
        return new BaseMethods(
            'shuffle',
            phpCodeForConversion: 'str_shuffle(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function splitEvery() {
        return new BaseMethods(
             'splitEvery',
            phpCodeForConversion: 'str_split(@self, @counting)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@counting', 'int', false, 1),
            ]
        );
    }

    public function wordCount() {
        return new BaseMethods(
             'wordCount',
            phpCodeForConversion: 'str_word_count(@self, @format, @characters)',
            returnOfPhpExecution: ['Int'],
            params: [
                new BaseParams('@format', 'int', false, 0),
                new BaseParams('@characters', '?int', false, null),
            ]
        );
    }

    public function split() {
        return new BaseMethods(
            'split',
            phpCodeForConversion: 'explode(@separator, @self, @limit)',
            returnOfPhpExecution: ['Array'],
            subTypes: ['String'],
            params: [
                new BaseParams('@separator', 'string', true),
                new BaseParams('@limit', 'int', false, 1),
            ],
        );
    }
}
