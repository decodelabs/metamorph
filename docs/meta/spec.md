# Metamorph — Package Specification

> **Cluster:** `frontend`
> **Language:** `php`
> **Milestone:** `m5`
> **Repo:** `https://github.com/decodelabs/metamorph`
> **Role:** Content transformation system

## Overview

### Purpose

Metamorph provides a flexible framework for content transformations in PHP applications. It enables converting content from one format to another through a simple and intuitive interface. Metamorph supports:

- Content format transformation via handler system
- Named macro bundles for common transformation patterns
- Extensible handler architecture via Archetype
- Built-in handlers for text, Markdown, and HTML-to-text conversion
- URL resolution in transformed content
- Integration with Tagged for HTML output
- Optional library support for enhanced functionality

Metamorph is designed to provide a unified API for content transformations, allowing applications to convert between formats (e.g., Markdown to HTML, HTML to text) with consistent configuration and extensibility.

### Non-Goals

- Metamorph does not provide template rendering or templating engines
- It does not handle file-based transformations (works with string content)
- It does not provide caching or performance optimization
- It does not handle complex document structures (focused on simple content)
- It does not provide validation or sanitization (delegates to handlers)
- It does not support streaming or large file processing

## Role in the Ecosystem

### Cluster & Positioning

Metamorph belongs to the **frontend** cluster, providing content transformation capabilities for frontend rendering. It sits alongside other frontend packages and is used for converting content formats in web applications.

### Usage Contexts

Metamorph is used for:

- Markdown to HTML conversion
- HTML to plain text conversion
- Text formatting and truncation
- Content preview generation
- Format normalization
- Content transformation pipelines
- User-generated content processing

## Public Surface

### Key Types

- **`Metamorph`** — Main facade class providing static methods for content transformation. Supports magic method calls for handler invocation.

- **`Metamorph\Handler`** — Interface for content transformation handlers. Defines `convert()` method for transforming content.

- **`Metamorph\MacroHandler`** — Interface extending `Handler` for handlers that support named macro bundles.

- **`Metamorph\MacroHandlerTrait`** — Trait providing default macro loading implementation.

- **`Metamorph\Handler\Text`** — Handler for plain text transformation. Supports truncation, HTML wrapping, and ellipsis.

- **`Metamorph\Handler\Markdown`** — Handler for Markdown to HTML conversion. Supports inline mode, safe mode, and URL resolution.

- **`Metamorph\Handler\HtmlToText`** — Handler for HTML to plain text conversion. Supports truncation, HTML wrapping, and ellipsis.

- **`Metamorph\Handler\HtmlTrait`** — Trait providing URL resolution functionality for HTML content.

### Main Entry Points

- **`Metamorph::convert(string $name, mixed $content, ?array $options, ?callable $setup): string|Stringable|null`** — Transforms content using the specified handler. Returns transformed content or null.

- **`Metamorph::__callStatic(string $method, array $args): string|Stringable|null`** — Magic method for calling handlers via static method syntax (e.g., `Metamorph::markdown()`).

- **`Metamorph::loadHandler(string $name, ?array $options): Handler`** — Loads a handler by name with options. Supports macro syntax (e.g., `markdown.safe`).

- **`Metamorph::setUrlResolver(?Closure $resolver): void`** — Sets a global URL resolver for transforming URLs in HTML content.

- **`Metamorph::resolveUrl(string $url): string`** — Resolves a URL using the configured resolver.

- **`Handler::convert(string $content, ?callable $setup): string|Stringable|null`** — Transforms content. Setup callback receives handler instance for configuration.

- **`MacroHandler::loadMacro(string $name): ?array`** — Static method for loading macro options by name.

- **`Text::convert(string $content, ?callable $setup): string|Stringable|null`** — Transforms plain text with optional truncation and HTML wrapping.

- **`Markdown::convert(string $content, ?callable $setup): string|Stringable|null`** — Converts Markdown to HTML with optional inline mode and safe mode.

- **`HtmlToText::convert(string $content, ?callable $setup): string|Stringable|null`** — Converts HTML to plain text with optional truncation and HTML wrapping.

## Dependencies

### Decode Labs

- **`archetype`** — Used for automatic handler discovery and loading.

- **`coercion`** — Used for type coercion when processing handler options.

- **`exceptional`** — Used for exception handling throughout the package.

- **`monarch`** — Used for accessing Archetype service.

- **`tagged`** — Used for HTML output generation and content normalization.

### External

- **`symfony/polyfill-mbstring`** — Used for multibyte string operations.

### Optional Dependencies

- **`erusev/parsedown`** — Detected at runtime if installed, used for Markdown parsing (preferred).

- **`michelf/php-markdown`** — Detected at runtime if installed, used for Markdown parsing (fallback for non-inline mode).

- **`soundasleep/html2text`** — Detected at runtime if installed, used for better HTML to text conversion.

## Behaviour & Contracts

### Invariants

- Handlers are loaded via Archetype using handler name
- Macro names are separated by dots (e.g., `markdown.safe`)
- Macros are merged with explicit options (explicit options override macro options)
- Content is normalized to string before transformation
- Null content returns null
- Empty content may return null depending on handler
- URL resolution is applied to HTML output when enabled
- Handlers return string, Stringable, or null

### Input & Output Contracts

- **`Metamorph::convert(string $name, mixed $content, ?array $options, ?callable $setup): string|Stringable|null`** — Transforms content. Returns transformed content or null. Throws exception if handler not found.

- **`Metamorph::loadHandler(string $name, ?array $options): Handler`** — Loads handler by name. Supports macro syntax (`name.macro`). Returns handler instance. Throws exception if handler not found.

- **`Metamorph::setUrlResolver(?Closure $resolver): void`** — Sets global URL resolver. Resolver receives URL string and returns resolved URL string.

- **`Metamorph::resolveUrl(string $url): string`** — Resolves URL using configured resolver. Returns original URL if no resolver set or resolver throws exception.

- **`Handler::convert(string $content, ?callable $setup): string|Stringable|null`** — Transforms content. Setup callback receives handler instance. Returns transformed content or null.

- **`MacroHandler::loadMacro(string $name): ?array`** — Returns macro options array or null if macro not found.

- **`Text::convert(string $content, ?callable $setup): string|Stringable|null`** — Transforms text. Returns null if content is empty. Applies truncation and wrapping based on options.

- **`Markdown::convert(string $content, ?callable $setup): string|Stringable|null`** — Converts Markdown to HTML. Uses Parsedown if available, otherwise MarkdownLib. Throws `ComponentUnavailable` if no Markdown library available.

- **`HtmlToText::convert(string $content, ?callable $setup): string|Stringable|null`** — Converts HTML to text. Uses Html2Text if available, otherwise strip_tags. Returns null if content is empty after stripping.

## Error Handling

Metamorph uses the Exceptional pattern for error handling. Key exception types:

- **`ComponentUnavailable`** — Thrown when a required library is not available (e.g., no Markdown library for Markdown handler).

Exceptions preserve the original service context and include detailed error messages. URL resolution errors are silently caught and original URL is returned.

## Configuration & Extensibility

### Extension Points

- **Custom Handlers** — Implement `Handler` interface in `DecodeLabs\Metamorph\Handler` namespace or provide custom Archetype resolver.

- **Macro Support** — Implement `MacroHandler` interface and define `Macros` constant to provide named option bundles.

- **URL Resolution** — Set global URL resolver via `setUrlResolver()` to transform URLs in HTML output.

- **Handler Setup** — Provide setup callbacks to `convert()` for runtime handler configuration.

### Configuration

- **Handler Loading** — Handlers are loaded via Archetype using handler name. Names are resolved to handler class names.

- **Macro Syntax** — Macros are specified using dot notation (e.g., `markdown.safe`). Multiple macros can be chained (e.g., `markdown.inline.safe`).

- **Option Merging** — Macro options are merged with explicit options, with explicit options taking precedence.

- **Content Normalization** — Content is normalized to string before transformation. Stringable objects and Tagged content are converted to strings.

- **URL Resolution** — URL resolution is enabled by default for handlers using `HtmlTrait`. Can be disabled via `resolveUrls` option.

## Interactions with Other Packages

- **Archetype** — Used for automatic handler discovery and loading.

- **Monarch** — Used for accessing Archetype service.

- **Tagged** — Used for HTML output generation and content normalization.

- **Coercion** — Used for type coercion when processing handler options.

- **Exceptional** — Used for exception handling throughout the package.

- **Parsedown** — Optional integration for Markdown parsing (preferred).

- **MarkdownLib** — Optional integration for Markdown parsing (fallback).

- **Html2Text** — Optional integration for HTML to text conversion.

## Usage Examples

### Basic Transformation

```php
use DecodeLabs\Metamorph;

// Markdown to HTML
$html = Metamorph::markdown('# Hello World');

// Text with options
$text = Metamorph::text('Hello world', [
    'maxLength' => 5,
    'ellipsis' => '...',
    'wrap' => true
]);

// HTML to text
$text = Metamorph::htmlToText('<p>HTML content</p>');
```

### Macro Usage

```php
use DecodeLabs\Metamorph;

// Use macro for common patterns
$html = Metamorph::{'markdown.safe'}($markdownContent);

// Inline Markdown
$html = Metamorph::{'markdown.inline'}($markdownContent);

// Combined macros
$html = Metamorph::{'markdown.inline.safe'}($markdownContent);

// Text macros
$text = Metamorph::{'text.raw'}($longText);
$text = Metamorph::{'text.preview'}($longText);
$text = Metamorph::{'text.preview.raw'}($longText);

// HtmlToText macros
$text = Metamorph::{'htmlToText.wrap'}($html);
$text = Metamorph::{'htmlToText.preview'}($html);
```

### Handler Setup

```php
use DecodeLabs\Metamorph;

// Configure handler via setup callback
$html = Metamorph::markdown($markdownContent, [], function($handler) {
    $handler->safe = true;
    $handler->inline = false;
});
```

### URL Resolution

```php
use DecodeLabs\Metamorph;

// Set global URL resolver
Metamorph::setUrlResolver(function(string $url): string {
    // Transform relative URLs to absolute
    if (!str_starts_with($url, 'http')) {
        return 'https://example.com/' . ltrim($url, '/');
    }
    return $url;
});

// URLs in HTML output will be resolved
$html = Metamorph::markdown('[Link](page.html)');
// Result: <a href="https://example.com/page.html">Link</a>
```

### Custom Handler

```php
use DecodeLabs\Metamorph\Handler;
use DecodeLabs\Metamorph\MacroHandler;
use DecodeLabs\Metamorph\MacroHandlerTrait;

class MyHandler implements MacroHandler
{
    use MacroHandlerTrait;

    public const array Macros = [
        'option1' => ['key' => 'value1'],
        'option2' => ['key' => 'value2']
    ];

    public function __construct(
        protected array $options
    ) {
    }

    public function convert(
        string $content,
        ?callable $setup = null
    ): string|null {
        // Transform content
        return $transformed;
    }
}

// Use custom handler
$result = Metamorph::myHandler($content, ['key' => 'value']);
$result = Metamorph::{'myHandler.option1'}($content);
```

### Text Handler Options

```php
use DecodeLabs\Metamorph;

// Truncate with ellipsis
$text = Metamorph::text($longText, [
    'maxLength' => 50,
    'ellipsis' => '...'
]);

// Wrap in HTML
$html = Metamorph::text($text, ['wrap' => true]);

// Raw text (no wrapping)
$text = Metamorph::text($text, ['wrap' => false]);
```

### Markdown Handler Options

```php
use DecodeLabs\Metamorph;

// Safe mode (sanitize output)
$html = Metamorph::markdown($markdown, ['safe' => true]);

// Inline mode (no block elements)
$html = Metamorph::markdown($markdown, ['inline' => true]);

// Disable URL resolution
$html = Metamorph::markdown($markdown, ['resolveUrls' => false]);
```

### HtmlToText Handler Options

```php
use DecodeLabs\Metamorph;

// Strip HTML and wrap in markup
$text = Metamorph::htmlToText($html, [
    'wrap' => true,
    'maxLength' => 100
]);

// Raw text output
$text = Metamorph::htmlToText($html, ['wrap' => false]);
```

## Implementation Notes (for Contributors)

### Architecture

- **Handler System** — Handlers are loaded via Archetype, allowing automatic discovery and custom resolvers.

- **Macro System** — Macros provide named option bundles, allowing common patterns to be reused via dot notation.

- **Static Facade** — The `Metamorph` class provides a static facade with magic methods for convenient handler invocation.

- **Content Normalization** — Content is normalized to string before transformation, supporting Stringable objects and Tagged content.

- **URL Resolution** — URL resolution is provided via `HtmlTrait` and global resolver, allowing URLs in HTML to be transformed.

- **Library Detection** — Markdown and HtmlToText handlers detect available libraries at runtime, providing fallbacks when possible.

- **Option Merging** — Macro options are merged with explicit options, with explicit options taking precedence.

- **Setup Callbacks** — Setup callbacks allow runtime handler configuration after instantiation.

### Performance Considerations

- Handlers are instantiated on each call (no caching)
- Content normalization happens before transformation
- URL resolution uses regex for HTML attribute matching
- Library detection happens on each handler instantiation

### Design Decisions

- **Static Facade** — Using static methods with magic method calls provides a convenient API while maintaining extensibility.

- **Macro System** — Providing macros allows common patterns to be reused without repeating option arrays.

- **Handler Interface** — Using a simple interface allows easy extension while maintaining consistency.

- **Library Detection** — Detecting libraries at runtime allows optional dependencies without requiring all libraries.

- **URL Resolution** — Providing URL resolution via trait and global resolver allows flexible URL transformation.

- **Content Normalization** — Normalizing content to string before transformation simplifies handler implementation.

- **Tagged Integration** — Using Tagged for HTML output provides type-safe HTML generation.

## Testing & Quality

**Code Quality:** 3.5/5 — Good codebase with solid structure and functionality. Some areas may benefit from additional features or optimizations.

**README Quality:** 3/5 — Good documentation with clear usage examples covering main use cases.

**Documentation:** 0/5 — No formal documentation beyond README.

**Tests:** 0/5 — No test suite currently.

See `composer.json` for supported PHP versions.

## Roadmap & Future Ideas

- Enhanced documentation and API reference
- Test suite implementation
- Handler caching for performance
- Additional built-in handlers
- Streaming support for large content
- Content validation integration
- Performance optimizations
- Additional macro patterns
- Handler composition support

## References

- [Decode Labs Chorus](https://github.com/decodelabs/chorus)
- [Metamorph Repository](https://github.com/decodelabs/metamorph)
- [Idiom Repository](https://github.com/decodelabs/idiom) — Custom Metamorph handler implementation
- [Chirp Repository](https://github.com/decodelabs/chirp) — Custom Metamorph handler implementation

