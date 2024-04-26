<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph;

trait MacroHandlerTrait
{
    //const MACROS = [];

    /**
     * Load macro options
     *
     * @return array<string, mixed>|null
     */
    public static function loadMacro(
        string $name
    ): ?array {
        if (
            !isset(static::MACROS[$name]) ||
            !is_array(static::MACROS[$name])
        ) {
            return null;
        }

        return static::MACROS[$name];
    }
}
