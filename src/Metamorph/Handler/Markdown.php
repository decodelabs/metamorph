<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Handler;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Metamorph\MacroHandlerTrait;
use DecodeLabs\Tagged\Buffer;

use Michelf\Markdown as MarkdownLib;
use Parsedown;

use Stringable;

class Markdown implements MacroHandler
{
    use MacroHandlerTrait;
    use HtmlTrait;

    public const MACROS = [
        'safe' => [
            'safe' => true
        ],
        'inline' => [
            'inline' => true
        ],
        'inline.safe' => [
            'safe' => true,
            'inline' => true
        ],
    ];

    protected bool $inline = false;
    protected bool $safe = false;

    /**
     * Set options
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->inline = Coercion::toBool($options['inline'] ?? $this->inline);
        $this->safe = Coercion::toBool($options['safe'] ?? $this->safe);
        $this->resolveUrls = Coercion::toBool($options['resolveUrls'] ?? $this->resolveUrls);
    }



    /**
     * Convert markdown to HTML
     */
    public function convert(
        string $content,
        ?callable $setup = null
    ): string|Stringable|null {
        if (class_exists(Parsedown::class)) {
            return $this->convertParsedown($content, $setup);
        }

        if (
            !$this->inline &&
            class_exists(MarkdownLib::class)
        ) {
            return $this->convertMarkdownLib($content, $setup);
        }

        throw Exceptional::ComponentUnavailable(
            'No supported Markdown processors could be found for the requested format - try installing Parsedown'
        );
    }

    /**
     * Convert markdown using Parsedown
     */
    protected function convertParsedown(
        string $content,
        ?callable $setup = null
    ): string|Stringable {
        $parser = new Parsedown();
        $parser->setSafeMode(!$this->safe);

        if ($setup) {
            $setup($parser);
        }

        if ($this->inline) {
            $output = $parser->line($content);
        } else {
            $output = $parser->text($content);
        }

        $output = $this->resolveHtmlUrls($output);
        return new Buffer($output);
    }

    /**
     * Convert markdown using Markdown lib
     */
    protected function convertMarkdownLib(
        string $content,
        ?callable $setup = null
    ): string|Stringable {
        $parser = new MarkdownLib();

        if (!$this->safe) {
            $parser->no_markup = true;
            $parser->no_entities = true;
        }

        if ($setup) {
            $setup($parser);
        }

        $output = $parser->transform($content);
        $output = $this->resolveHtmlUrls($output);
        return new Buffer($output);
    }
}
