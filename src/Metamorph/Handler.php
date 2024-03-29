<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph;

use Stringable;

interface Handler
{
    /**
     * @param callable(object):void|null $setup
     */
    public function convert(
        string $content,
        ?callable $setup = null
    ): string|Stringable|null;
}
