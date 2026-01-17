<?php

declare(strict_types=1);

namespace PHPScript\Core;

enum CompileMode: string
{
    case BUILD   = 'build';
    case SNAPSHOT   = 'snapshot';
    case WATCH   = 'watch';
    case DEBUG   = 'debug';
    case TEST    = 'test';
    case CHECK   = 'check';
}
