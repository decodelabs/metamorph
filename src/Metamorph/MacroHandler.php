<?php

/**
 * Metamorph
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph;

interface MacroHandler extends Handler
{
    /**
     * @var array<string,array<string,mixed>>
     */
    public const array Macros = [];

    /**
     * @return array<string,mixed>|null
     */
    public static function loadMacro(
        string $name
    ): ?array;
}
