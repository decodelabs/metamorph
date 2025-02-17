<?php

/**
 * @package Metamorph
 * @license http://opensource.org/licenses/MIT
 */

declare(strict_types=1);

namespace DecodeLabs\Metamorph\Tests;

use DecodeLabs\Metamorph;
use Stringable;

class AnalyzeFactoryCall {

    public function render(): string|Stringable {
        return Metamorph::{'markdown.safe'}('Hello World');
    }
}

$obj = new AnalyzeFactoryCall();
$obj->render();
