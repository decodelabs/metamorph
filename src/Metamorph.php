<?php

/**
 * Metamorph
 * @license https://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use Closure;
use DecodeLabs\Metamorph\Handler;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Tagged\ContentCollection;
use ReflectionClass;
use Stringable;
use Throwable;

class Metamorph
{
    /**
     * @var Closure(string):string|null
     */
    protected static ?Closure $urlResolver = null;

    /**
     * @param Closure(string):string|null $resolver
     */
    public static function setUrlResolver(
        ?Closure $resolver
    ): void {
        static::$urlResolver = $resolver;
    }

    public static function resolveUrl(
        string $url
    ): string {
        if (null === ($resolver = static::$urlResolver)) {
            return $url;
        }

        try {
            $url = $resolver($url);
        } catch (Throwable $e) {
        }

        return $url;
    }



    /**
     * @param array{0: mixed, 1?: array<string, mixed>, 2?: callable(Handler):void|null} $args
     */
    public static function __callStatic(
        string $method,
        array $args
    ): string|Stringable|null {
        return static::convert($method, ...$args);
    }

    /**
     * @param callable(object):void|null $setup
     * @param array<string, mixed>|null $options
     */
    public static function convert(
        string $name,
        mixed $content,
        ?array $options = [],
        ?callable $setup = null
    ): string|Stringable|null {
        if ($content === null) {
            return null;
        }

        if (
            null === ($content = static::prepareContent($content))
        ) {
            return null;
        }

        $handler = static::loadHandler($name, $options);
        return $handler->convert($content, $setup);
    }

    /**
     * @param mixed $content
     */
    protected static function prepareContent(
        mixed $content
    ): ?string {
        if (
            is_string($content) ||
            $content instanceof Stringable
        ) {
            return (string)$content;
        }

        return (string)ContentCollection::normalize($content);
    }

    /**
     * @param array<string,mixed>|null $options
     */
    public static function loadHandler(
        string $name,
        ?array $options = []
    ): Handler {
        $parts = explode('.', $name, 2);
        $name = $parts[0];
        $macro = $parts[1] ?? null;

        $archetype = Monarch::getService(Archetype::class);
        $class = $archetype->resolve(Handler::class, ucfirst($name));
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
