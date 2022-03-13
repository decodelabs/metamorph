<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Metamorph\Handler;
use DecodeLabs\Metamorph\MacroHandler;
use ReflectionClass;
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
        $parts = explode('.', $name, 2);
        $name = $parts[0];
        $macro = $parts[1] ?? null;

        /** @var class-string<Handler> */
        $class = Archetype::resolve(Handler::class, ucfirst($name));
        $reflection = new ReflectionClass($class);

        if (
            $reflection->implementsInterface(MacroHandler::class) &&
            $macro !== null
        ) {
            /** @var class-string<MacroHandler> $class */
            $options = array_merge($class::loadMacro($macro) ?? [], $options ?? []);
        }

        return new $class($options ?? []);
    }
}
