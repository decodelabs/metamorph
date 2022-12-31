# Metamorph

[![PHP from Packagist](https://img.shields.io/packagist/php-v/decodelabs/metamorph?style=flat)](https://packagist.org/packages/decodelabs/metamorph)
[![Latest Version](https://img.shields.io/packagist/v/decodelabs/metamorph.svg?style=flat)](https://packagist.org/packages/decodelabs/metamorph)
[![Total Downloads](https://img.shields.io/packagist/dt/decodelabs/metamorph.svg?style=flat)](https://packagist.org/packages/decodelabs/metamorph)
[![GitHub Workflow Status](https://img.shields.io/github/actions/workflow/status/decodelabs/metamorph/integrate.yml?branch=develop)](https://github.com/decodelabs/metamorph/actions/workflows/integrate.yml)
[![PHPStan](https://img.shields.io/badge/PHPStan-enabled-44CC11.svg?longCache=true&style=flat)](https://github.com/phpstan/phpstan)
[![License](https://img.shields.io/packagist/l/decodelabs/metamorph?style=flat)](https://packagist.org/packages/decodelabs/metamorph)

### A flexible framework for content transformations

Metamorph provides an extensible framework for transforming content from one format to another through a simple and intuitive interface.

_Get news and updates on the [DecodeLabs blog](https://blog.decodelabs.com)._

---


## Installation

```bash
composer require decodelabs/metamorph
```

## Usage

### Importing

Metamorph uses [Veneer](https://github.com/decodelabs/veneer) to provide a unified frontage under <code>DecodeLabs\Metamorph</code>.
You can access all the primary functionality via this static frontage without compromising testing and dependency injection.


### Transformations

Use the Metamorph frontage to manipulate and output different formats of content.

Options can be passed as the second parameter and named macros allow for bundles of options to be passed as part of the call.

Metamorph uses [Archetype](https://github.com/decodelabs/archetype) to load `Handler` classes - the base package comes with a small set of handlers out of the box:


### Markdown

The built-in Markdown Handler will use whichever appropriate Markdown library has been installed via composer.

```php
use DecodeLabs\Metamorph;

// Default Markdown renders
echo Metamorph::markdown($markdownContent);

// Ensure output is secure from exploits with "safe" macro
echo Metamorph::{'markdown.safe'}($markdownContent);

// Output inline markup
echo Metamorph::{'markdown.inline'}($markdownContent);
```


### Text

The Text Handler considers input to be plain text and allows for various manipulations.
HTML wrapping is turned on by default, converting the text to Tagged Markup.

```php
echo Metamorph::text('Hello world', [
    'maxLength' => 5 // shorten output with ellipsis
    'ellipsis' => '...' // Character(s) used while shortening string (optional)
    'wrap' => true // Wrap output as HTML markup
]);

// wrap=false
echo Metamorph::{'text.raw'}($longText);

// maxLength=50, wrap=true
echo Metamorph::{'text.preview'}($longText);

// maxLength=50, wrap=false
echo Metamorph::{'text.preview.raw'}($longText);
```


### HtmlToText

The HtmlToText Handler works in the opposite direction, taking HTML input and converting it to readable plain text.

```php
echo Metamorph::htmlToText('<p>This is an HTML paragraph</p>', [
    'maxLength' => 5 // shorten stripped output with ellipsis
    'ellipsis' => '...' // Character(s) used while shortening string (optional)
    'wrap' => true // Wrap the stripped text in Markup element
]);

// Strip and re-wrap HTML
echo Metamorph::{'htmlToText.wrap'}($html);

// maxLength=50, wrap=true
echo Metamorph::{'htmlToText.preview'}($html);
```



## Other implementations

See [Idiom](https://github.com/decodelabs/idiom) and [Chirp](https://github.com/decodelabs/chirp) for other custom implementations of Metamorph Handlers.


## Licensing
Metamorph is licensed under the MIT License. See [LICENSE](./LICENSE) for the full license text.
