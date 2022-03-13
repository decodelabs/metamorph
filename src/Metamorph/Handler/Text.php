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
use Stringable;

class Text implements MacroHandler
{
    use MacroHandlerTrait;

    public const MACROS = [
        'raw' => [
            'wrap' => false
        ],
        'preview' => [
            'maxLength' => 50
        ],
        'preview.raw' => [
            'maxLength' => 50,
            'wrap' => false
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
    protected $wrap = true;

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
    protected function escape(string $content): ?string
    {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
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
                    new Buffer(str_replace("\n", '<br />' . "\n", $this->shorten($this->escape($content)))),
                    Element::create('span.ellipsis', $this->ellipsis)
                ], [
                    'title' => $content
                ])
            ];

            return ContentCollection::normalize($content);
        }

        return new Buffer(str_replace("\n", '<br />' . "\n", $this->escape($content)));
    }
}
