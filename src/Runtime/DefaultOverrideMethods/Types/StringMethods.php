<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Helper\Debug\Debug;
use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;
use PHireScript\Runtime\DefaultOverrideMethods\BaseParams;

class StringMethods extends GeneralType {
    public function length() {
        return new BaseMethods(
            'strlen(@self)',
            ['Int'],
        );
    }

    public function toUpperCase() {
        return new BaseMethods(
            phpCodeForConversion: 'mb_strtoupper(@self, @format)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@format', 'string', false, 'UTF-8'),
            ],
        );
    }

    public function toLowerCase() {
        return new BaseMethods(
            phpCodeForConversion: 'mb_strtolower(@self, @format)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@format', 'string', false, 'UTF-8'),
            ],
        );
    }

    public function replace() {
        return new BaseMethods(
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
            phpCodeForConversion: 'trim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeSpacesLeft() {
        return new BaseMethods(
            phpCodeForConversion: 'ltrim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeSpacesRight() {
        return new BaseMethods(
            phpCodeForConversion: 'rtrim(@self, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@characters', 'string', false, null),
            ]
        );
    }

    public function removeAllSpaces() {
        return new BaseMethods(
            phpCodeForConversion: "preg_replace('/\s+/', '', @self)",
            returnOfPhpExecution: ['String'],
        );
    }

    public function contains() {
        return new BaseMethods(
            phpCodeForConversion: 'str_contains(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function endWith() {
        return new BaseMethods(
            phpCodeForConversion: 'str_ends_with(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function startWith() {
        return new BaseMethods(
            phpCodeForConversion: 'str_starts_with(@self, @characters)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@characters', 'string', true),
            ]
        );
    }

    public function decrement() {
        return new BaseMethods(
            phpCodeForConversion: 'str_decrement(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function increment() {
        return new BaseMethods(
            phpCodeForConversion: 'str_increment(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function getCsv() {
        return new BaseMethods(
            phpCodeForConversion: 'str_getcsv(@self, @separator, @enclosure, @escape)',
            returnOfPhpExecution: ['Bool'],
            params: [
                new BaseParams('@separator', 'string', false, ','),
                new BaseParams('@enclosure', 'string', false, "\""),
                new BaseParams('@escape', 'string', false, "\\"),
            ]
        );
    }

    public function join() {
        return new BaseMethods(
            phpCodeForConversion: '@self . implode(\'\', [@params])',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@params', 'string', true),
            ]
        );
    }

    public function repeat() {
        return new BaseMethods(
            phpCodeForConversion: 'str_repeat("@self, @times)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@times', 'int', true),
            ]
        );
    }

    public function shuffle() {
        return new BaseMethods(
            phpCodeForConversion: 'str_shuffle(@self)',
            returnOfPhpExecution: ['String'],
        );
    }

    public function splitAtEvery() {
        return new BaseMethods(
            phpCodeForConversion: 'str_split(@self, @counting)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@counting', 'int', false, 1),
            ]
        );
    }

    public function wordCount() {
        return new BaseMethods(
            phpCodeForConversion: 'str_word_count(@self, @format, @characters)',
            returnOfPhpExecution: ['String'],
            params: [
                new BaseParams('@format', 'int', false, 0),
                new BaseParams('@characters', '?int', false, null),
            ]
        );
    }

    public function split() {
        return new BaseMethods(
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
