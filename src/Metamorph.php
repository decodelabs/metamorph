<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Metamorph\Handler;

use Stringable;

class Metamorph
{
    /**
     * Initiate conversion
     *
     * @param array{0: string|null, 1?: array<string, mixed>, 2?: callable(Handler):void|null} $args
     * @return string|Stringable|null
     */
    public static function __callStatic(string $method, array $args)
    {
        return static::convert($method, ...$args);
    }

    /**
     * Handle conversion
     *
     * @param callable(object):void|null $setup
     * @param array<string, mixed>|null $options
     * @return string|Stringable|null
     */
    public static function convert(
        string $name,
        ?string $content,
        ?array $options = [],
        ?callable $setup = null
    ) {
        if (
            $content === null ||
            !strlen($content)
        ) {
            return null;
        }

        $handler = static::loadHandler($name, $options);
        return $handler->convert($content, $setup);
    }

    /**
     * Load handler
     *
     * @param array<string, mixed>|null $options
     */
    public static function loadHandler(
        string $name,
        ?array $options = []
    ): Handler {
        /** @var class-string<Handler> */
        $class = Archetype::resolve(Handler::class, ucfirst($name));
        return new $class($options ?? []);
    }
}
