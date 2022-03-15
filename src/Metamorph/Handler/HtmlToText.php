<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Handler;

use DecodeLabs\Coercion;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Metamorph\MacroHandlerTrait;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Element;
use Soundasleep\Html2Text;
use Stringable;

class HtmlToText implements MacroHandler
{
    use MacroHandlerTrait;

    public const MACROS = [
        'wrap' => [
            'wrap' => true
        ],
        'preview' => [
            'maxLength' => 50,
            'wrap' => true
        ]
    ];


    /**
     * @var int|null
     */
    protected $maxLength = null;

    /**
     * @var string
     */
    protected $ellipsis = '…';

    /**
     * @var bool
     */
    protected $wrap = false;

    /**
     * Set options
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->maxLength = Coercion::toIntOrNull($options['maxLength'] ?? $this->maxLength);
        $this->wrap = Coercion::toBool($options['wrap'] ?? $this->wrap);
        $this->ellipsis = Coercion::toString($options['ellipsis'] ?? $this->ellipsis);
    }

    /**
     * Convert input to plain text
     */
    public function convert(
        string $content,
        ?callable $setup = null
    ) {
        $content = trim($content);

        if (!strlen($content)) {
            return null;
        }

        if ($setup) {
            $setup($this);
        }

        $content = $this->strip($content);

        if ($content === null) {
            return $content;
        }

        $content = (string)$content;

        $shorten =
            $this->maxLength > 0 &&
            mb_strlen($content) > $this->maxLength;

        return $this->wrap($content, $shorten);
    }


    /**
     * Strip significant characters from content
     */
    protected function strip(string $content): ?string
    {
        $content = new Buffer($content);
        $content = (string)ContentCollection::normalize($content);

        if (!strlen($content)) {
            return null;
        }

        if (class_exists(Html2Text::class)) {
            return Html2Text::convert($content, [
                'ignore_errors' => true
            ]);
        } else {
            $output = html_entity_decode(strip_tags($content), ENT_QUOTES | ENT_HTML5);
            return str_replace("\r\n", "\n", $output);
        }
    }


    /**
     * Shorten output string
     */
    protected function shorten(string $content): string
    {
        return mb_substr($content, 0, $this->maxLength);
    }


    /**
     * Wrap output content
     *
     * @return string|Stringable|null
     */
    protected function wrap(string $content, bool $shorten)
    {
        if ($this->wrap) {
            return $this->wrapHtml($content, $shorten);
        } else {
            return $this->wrapText($content, $shorten);
        }
    }


    /**
     * Wrap text content
     */
    protected function wrapText(string $content, bool $shorten): string
    {
        if ($shorten) {
            $content = $this->shorten($content) . $this->ellipsis;
        }

        return $content;
    }

    /**
     * Wrap HTML content
     *
     * @return string|Stringable|null
     */
    protected function wrapHtml(string $content, bool $shorten)
    {
        if ($shorten) {
            $content = [
                Element::create('abbr', [
                    new Buffer(str_replace("\n", '<br />' . "\n", $this->shorten($content))),
                    Element::create('span.ellipsis', $this->ellipsis)
                ], [
                    'title' => $content
                ])
            ];

            return ContentCollection::normalize($content);
        }

        return new Buffer(str_replace("\n", '<br />' . "\n", $content));
    }
}
