<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Handler;

use DecodeLabs\Metamorph;

trait HtmlTrait
{
    /**
     * @var bool
     */
    protected $resolveUrls = true;

    /**
     * Resolve URLs in HTML
     */
    protected function resolveHtmlUrls(string $html): string
    {
        if (!$this->resolveUrls) {
            return $html;
        }

        return preg_replace_callback('/ (href|src)\=\"([^\"]+)\"/', function (array $matches): string {
            return ' ' . $matches[1] . '="' . Metamorph::resolveUrl(html_entity_decode($matches[2])) . '"';
        }, $html) ?? $html;
    }
}
