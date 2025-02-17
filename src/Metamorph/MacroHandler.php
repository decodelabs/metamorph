<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
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
     * Load macro options
     *
     * @return array<string,mixed>|null
     */
    public static function loadMacro(
        string $name
    ): ?array;
}
