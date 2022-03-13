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
     * Load macro options
     *
     * @return array<string, mixed>|null
     */
    public static function loadMacro(string $name): ?array;
}
