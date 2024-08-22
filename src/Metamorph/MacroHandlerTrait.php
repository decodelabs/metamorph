<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph;

/**
 * @phpstan-require-implements MacroHandler
 */
trait MacroHandlerTrait
{
    //protected const Macros = [];

    /**
     * Load macro options
     *
     * @return array<string, mixed>|null
     */
    public static function loadMacro(
        string $name
    ): ?array {
        if (
            !isset(static::Macros[$name]) ||
            !is_array(static::Macros[$name])
        ) {
            return null;
        }

        return static::Macros[$name];
    }
}
