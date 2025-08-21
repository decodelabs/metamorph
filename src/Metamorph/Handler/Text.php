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

    public const array Macros = [
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


    protected ?int $maxLength = null;
    protected string $ellipsis = '…'; // @ignore-non-ascii
    protected bool $wrap = true;

    /**
     * @param array<string,mixed> $options
     */
    public function __construct(
        array $options
    ) {
        $this->maxLength = Coercion::tryInt($options['maxLength'] ?? $this->maxLength);
        $this->wrap = Coercion::toBool($options['wrap'] ?? $this->wrap);
        $this->ellipsis = Coercion::asString($options['ellipsis'] ?? $this->ellipsis);
    }

    public function convert(
        string $content,
        ?callable $setup = null
    ): string|Stringable|null {
        $content = trim($content);

        if (!strlen($content)) {
            return null;
        }

        if ($setup) {
            $setup($this);
        }

        $shorten =
            $this->maxLength > 0 &&
            mb_strlen($content) > $this->maxLength;

        return $this->wrap($content, $shorten);
    }


    protected function escape(
        string $content
    ): string {
        return htmlspecialchars($content, ENT_QUOTES, 'UTF-8');
    }


    protected function shorten(
        string $content
    ): string {
        return mb_substr($content, 0, $this->maxLength);
    }


    protected function wrap(
        string $content,
        bool $shorten
    ): string|Stringable|null {
        if ($this->wrap) {
            return $this->wrapHtml($content, $shorten);
        } else {
            return $this->wrapText($content, $shorten);
        }
    }


    protected function wrapText(
        string $content,
        bool $shorten
    ): string {
        if ($shorten) {
            $content = $this->shorten($content) . $this->ellipsis;
        }

        return $content;
    }

    protected function wrapHtml(
        string $content,
        bool $shorten
    ): string|Stringable|null {
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
