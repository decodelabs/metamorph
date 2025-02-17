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
    /**
     * Load macro options
     *
     * @return ?array<string,mixed>
     */
    public static function loadMacro(
        string $name
    ): ?array {
        return static::Macros[$name];
    }
}
