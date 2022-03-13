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
use DecodeLabs\Tagged as Html;
use DecodeLabs\Tagged\Buffer;
use Stringable;

class Escape implements Handler
{
    /**
     * @var string
     */
    protected $format = 'html';

    /**
     * Set options
     *
     * @param array<string, mixed> $options
     */
    public function __construct(array $options)
    {
        $this->format = Coercion::toString($options['format'] ?? $this->format);
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
                return $this->convertHtml($content);

            default:
                throw Exceptional::ComponentUnavailable(
                    'Unable to escape content in ' . $this->format . ' format'
                );
        }
    }

    /**
     * Convert HTML to escaped plain text
     *
     * @return string|Stringable|null
     */
    protected function convertHtml(string $content)
    {
        $content = Html::esc($content);
        $content = str_replace("\n", '<br />' . "\n", (string)$content);

        return new Buffer($content);
    }
}
