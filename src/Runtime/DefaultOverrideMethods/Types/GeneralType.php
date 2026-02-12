<?php

declare(strict_types=1);

namespace PHireScript\Runtime\DefaultOverrideMethods\Types;

use PHireScript\Runtime\DefaultOverrideMethods\BaseMethods;

class GeneralType
{
    public function destroy()
    {
        return new BaseMethods(
            'unset(@self)',
            ['null'],
        );
    }
}
