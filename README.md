# LiquidRazor Regex

An intuitive, small, and fast **regular expression helper for PHP 8.3+** with clean result objects and a few ergonomic utilities around PCRE.

> Package: `liquidrazor/regex` • License: MIT

---

## ✨ Features

- **Fluent, tiny API** for matching and replacing.
- **Lazy, rewindable match results** with `Iterator`, `ArrayAccess`, `Countable`, and `JsonSerializable` support.
- **Deterministic result shapes** via `ResultTypes` (numeric, associative, or both) and a `STRICT` filter.
- **PCRE helpers** to check version/JIT support and validate pattern syntax.
- **Exceptions you can catch** with informative messages bubbled from PCRE.

---

## 🚧 Requirements

- PHP **8.3** or newer
- PCRE (bundled with PHP)

---

## 📦 Installation

```bash
composer require liquidrazor/regex
```

The library is namespaced under `LiquidRazor\Regex` and PSR‑4 autoloaded per `composer.json`.

---

## 🧠 Concepts at a Glance

### `Regex` — main entry point
The `Regex` class is the main façade. It also memoizes compiled instances by pattern:

```php
use LiquidRazor\Regex\Regex;

$re = Regex::compiled('/(?P<word>[a-z]+)/i');
```

From there you can **match** or **replace** (see examples below).

### `ResultTypes` — control your result shape
Choose how match arrays are exposed:

```php
use LiquidRazor\Regex\Result\ResultTypes;

// Types
ResultTypes::INDEXED;      // numeric keys only (0, 1, 2, ...)
ResultTypes::ASSOCIATIVE;  // named keys only ('foo', 'bar', ...)
ResultTypes::BOTH;         // default: include both kinds

// Modifier
ResultTypes::STRICT;       // when used, filters to "pure" keys only:
//   - with INDEXED: keeps only truly integer keys (0,1,2...)
//   - with ASSOCIATIVE: keeps only truly string, non-numeric keys
```

This lets you request exactly the view you need (e.g., only named groups). You can bit‑OR flags:

```php
$flags = ResultTypes::ASSOCIATIVE | ResultTypes::STRICT;
```

### `Flags` (PCRE capture flags)
Extra capture behavior forwarded to PCRE:

```php
use LiquidRazor\Regex\Lib\Flags;

Flags::OffsetCapture;   // include offsets for each capture
Flags::UnmatchedAsNull; // unmatched subpatterns become null
```

---

## 🚀 Quick start

### 1) Find the first match

```php
use LiquidRazor\Regex\Regex;
use LiquidRazor\Regex\Result\ResultTypes;

$re = Regex::compiled('/(?P<word>[a-z]+)/i');

$result = $re->match(
    haystack: "Hello, World!",
    resultType: ResultTypes::ASSOCIATIVE | ResultTypes::STRICT // named keys only
);

if ($result->didMatch) {
    // ArrayAccess
    $first = $result[0]; // or $result['word'] in ASSOCIATIVE mode

    // Countable
    $count = count($result);

    // Iteration (rewindable)
    foreach ($result as $k => $v) {
        // ...
    }

    // JSON
    $json = json_encode($result, JSON_PRETTY_PRINT);
}
```

### 3) Replace

```php
use LiquidRazor\Regex\Regex;
use LiquidRazor\Regex\Result\Replace\Result;

$re = Regex::compiled('/(\d+)/');
$replace = $re->replace('abc 123 xyz 45', '#');

$replace->pattern;   // the pattern used
$replace->count;     // number of replacements performed
(string)$replace;    // casts to the replaced string (or JSON if array)
```

`LiquidRazor\Regex\Result\Replace\Result` implements `Stringable`. If the replacement result is an array (e.g., when replacing in arrays), `__toString()` returns JSON.

---

## 🎛️ Controlling result shapes

The `LiquidRazor\Regex\Result\Matches\Result` object exposes a lazy, memoized sequence of match entries. You decide which keys appear using `ResultTypes`:

- `INDEXED`: keeps only integer keys (0,1,2…). With `STRICT`, **filters out** any non-integer keys.
- `ASSOCIATIVE`: keeps only named group keys. With `STRICT`, **filters out** numeric-looking strings.
- `BOTH`: includes both numeric and associative keys (default). Combine with `STRICT` to prune mixed keys.

The object supports:

- `Iterator` (rewindable)
- `ArrayAccess` (read-only; attempting to mutate throws `ImmutableException`)
- `Countable`
- `JsonSerializable`

```php
// Read-only access (mutations throw)
$value = $result['name'] ?? $result[0];
```

If there was **no match**, the `Result` is empty with `didMatch === false` (safe to iterate/count/JSON-encode).

---

## 🧩 PCRE helpers

Use the `Pcre` static utility for environment checks:

```php
use LiquidRazor\Regex\Pcre;

Pcre::versionString();   // "PCRE x.y (major.minor)"
Pcre::isJitSupported();  // bool
Pcre::isValid('/^[a-z]+$/i'); // quick sanity check for "/.../flags"-style patterns
```

> Note: `isValid()` only checks a basic `/.../flags` shape, also does compile the pattern and checks the result. If compilation fails, it returns false.

---

## ⚠️ Exceptions

The library centralizes PCRE errors through `LiquidRazor\Regex\Exception` classes:

- `PcreException` – wraps `preg_last_error()` & message
- `Exception` – currently extends `PcreException` for convenience
- `ImmutableException` – thrown on attempts to mutate read-only results

In practice, most API calls either return a `Result` object or throw on **internal** PCRE errors (e.g., bad backtrack limit).

```php
use LiquidRazor\Regex\Exception\PcreException;

try {
    $re = Regex::compiled('/(?P<name>[a-z]+)/i');
    $res = $re->match('...');
} catch (PcreException $e) {
    // inspect $e->getMessage(), $e->getCode()
}
```

---

## 📚 API sketch

> The following reflects the current source structure at a high level. Some signatures may evolve.

- `Regex::compiled(string $pattern): Regex`
- `Regex->match(string $haystack, int $resultType = ResultTypes::BOTH, Flags ...$captureFlags): LiquidRazor\Regex\Result\Matches\Result`
- `Regex->replace(string|array $subject, string|callable $replacement, int $limit = -1): LiquidRazor\Regex\Result\Replace\Result`

Result types:

- `LiquidRazor\Regex\Result\Matches\Result`
  - Properties: `pattern`, `haystack`, `didMatch`
  - Interfaces: `Iterator`, `ArrayAccess` (read-only), `Countable`, `JsonSerializable`
- `LiquidRazor\Regex\Result\Replace\Result implements Stringable`
  - Properties: `pattern`, `count`, `replaced`
  - Casting: `(string)$result` yields the final string (or JSON if array)

Helpers:

- `LiquidRazor\Regex\Pcre`
  - `versionString(): string`
  - `isJitSupported(): bool`
  - `isValid(?string $pattern): bool`

Enums / constants:

- `LiquidRazor\Regex\Lib\Flags` — `Default`, `OffsetCapture`, `UnmatchedAsNull`
- `LiquidRazor\Regex\Result\ResultTypes` — `INDEXED`, `ASSOCIATIVE`, `BOTH`, `STRICT`

---

## ✅ Examples

### Only named groups, strictly

```php
use LiquidRazor\Regex\Regex;
use LiquidRazor\Regex\Result\ResultTypes;

$re = Regex::compiled('/(?P<num>\d+)-(?P<word>[a-z]+)/i');
$res = $re->match('42-Answer', ResultTypes::ASSOCIATIVE | ResultTypes::STRICT);

echo json_encode($res, JSON_PRETTY_PRINT);
/*
{
  "num": "42",
  "word": "Answer"
}
*/
```

### With offsets and nulls for unmatched

```php
use LiquidRazor\Regex\Regex;
use LiquidRazor\Regex\Lib\Flags;

$re = Regex::compiled('/(a)?(b)/');
$res = $re->match('xb', Flags::OffsetCapture, Flags::UnmatchedAsNull);

// Offsets included; the first group is null because it didn't match.
```

---

## 🧪 Testing

You can plug this library into any test runner. The result objects are easy to snapshot by JSON encoding.

```php
$this->assertJsonStringEqualsJsonString(
    '{"word":"Hello"}',
    json_encode($result, JSON_THROW_ON_ERROR)
);
```

---

## 🤝 Contributing

- Open an issue for design/API discussions.
- Keep the API minimal and predictable.
- PRs welcome with tests and docs for new behavior.

---

## 📄 License

[MIT](./LICENSE) © LiquidRazor
