<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Handler;

use DecodeLabs\Coercion;
use DecodeLabs\Exceptional;
use DecodeLabs\Metamorph\Handler;
use DecodeLabs\Tagged\Buffer;
use DecodeLabs\Tagged\ContentCollection;
use DecodeLabs\Tagged\Element;
use Soundasleep\Html2Text;
use Stringable;

class PlainText implements Handler
{
    /**
     * @var int|null
     */
    protected $maxLength = null;

    /**
     * @var string
     */
    protected $format = 'html';

    /**
     * @var string|null
     */
    protected $wrap = null;

    /**
     * @var string
     */
    protected $ellipsis = '…';

    /**
     * Set options
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->maxLength = Coercion::toIntOrNull($options['maxLength'] ?? $this->maxLength);
        $this->format = Coercion::toString($options['format'] ?? $this->format);
        $this->wrap = Coercion::toStringOrNull($options['wrap'] ?? $this->wrap);
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

        switch ($this->format) {
            case 'html':
                $content = $this->convertHtml($content);
                break;

            case null:
            case 'text':
                $content = $content;
                break;

            default:
                throw Exceptional::ComponentUnavailable(
                    'Unable to escape content in ' . $this->format . ' format'
                );
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
    * Convert HTML to plain text
    *
    * @return string|Stringable|null
    */
    protected function convertHtml(string $content)
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
        switch ($this->wrap) {
            case 'html':
                return $this->wrapHtml($content, $shorten);

            case null:
            case 'text':
                return $this->wrapText($content, $shorten);

            default:
                throw Exceptional::ComponentUnavailable(
                    'Unable to wrap content in ' . $this->format . ' format'
                );
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
                    $this->shorten($content),
                    Element::create('span.ellipsis', $this->ellipsis)
                ], [
                    'title' => $content
                ])
            ];
        }

        return ContentCollection::normalize($content);
    }
}
