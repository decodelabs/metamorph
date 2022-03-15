<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs;

use DecodeLabs\Metamorph\Handler;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Tagged\ContentCollection;
use ReflectionClass;
use Stringable;

class Metamorph
{
    /**
     * @var callable(string):string|null
     */
    protected static $urlResolver;

    /**
     * Set URL resolver
     *
     * @param callable(string):string|null $resolver
     */
    public static function setUrlResolver(?callable $resolver): void
    {
        static::$urlResolver = $resolver;
    }

    /**
     * Resolve URL
     */
    public static function resolveUrl(string $url): string
    {
        if (!$resolver = static::$urlResolver) {
            return $url;
        }

        try {
            $url = $resolver($url);
        } catch (Throwable $e) {
        }

        return $url;
    }



    /**
     * Initiate conversion
     *
     * @param array{0: mixed, 1?: array<string, mixed>, 2?: callable(Handler):void|null} $args
     * @return string|Stringable|null
     */
    public static function __callStatic(string $method, array $args)
    {
        return static::convert($method, ...$args);
    }

    /**
     * Handle conversion
     *
     * @param mixed $content
     * @param callable(object):void|null $setup
     * @param array<string, mixed>|null $options
     * @return string|Stringable|null
     */
    public static function convert(
        string $name,
        $content,
        ?array $options = [],
        ?callable $setup = null
    ) {
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
     * Prepare content for convert
     *
     * @param mixed $content
     */
    protected static function prepareContent($content): ?string
    {
        if (
            is_string($content) ||
            $content instanceof Stringable
        ) {
            return (string)$content;
        }

        return (string)ContentCollection::normalize($content);
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
