# Regex

**Regex** is a lightweight library for dealing and handling regular expressions in PHP.

It proivdes a clean and intuitive API that helps you perform common regular expression tasks safely.

---

## Example

```php

$inputData = [
    "invalid date",
    "",
    "2025 January 12 12:00:00",
    "invalid date",
    "2025 Ianuarie 12 12:00:00",
    "invalid date",
];

$monthNameToNumber = [
    "january" => 1,
    "ianuarie" => 1,
    "february" => 2,
    "februarie" => 2,
    "march" => 3,
    "martie" => 3,
    "april" => 4,
    "aprilie" => 4,
    "may" => 5,
    "mai" => 5,
    "june" => 6,
    "iunie" => 6,
    "july" => 7,
    "iulie" => 7,
    "august" => 8,
    "september" => 9,
    "septembrie" => 9,
    "october" => 10,
    "octombrie" => 10,
    "november" => 11,
    "noiembrie" => 11,
    "december" => 12,
    "decembrie" => 12,
];

$datePattern = "/(?P<year>\d+) (?P<month>\w+) (?P<day>\d+) (?P<hour>\d+):(?P<minute>\d+):(?P<second>\d+)/";

foreach ($inputData as $date) {
    // 1. Use `Regex::compiled()` method to cache the pattern instantiation for performance 
    $regex = Regex::compiled($datePattern);
    
    // 2. You can test whether a string matches the pattern using the `Regex::test` method
    if (false === $regex->test($date)) {
        continue;
    }
    
    // 3. get the result and have fun!
    $result = $regex->match($date);
    
    // 4. you can also check if the pattern did match
    if (false === $result->didMatch) {
        continue;
    }
    
    // 5. you can extract the values from the result via array access
    $year = $result['year'];
    $month = $result['month'];
    $day = $result['day'];
    $hour = $result['hour'];
    $minute = $result['minute'];
    $second = $result['second'];
    
    // 6. you can also perform the replace operation
    $dateObject = DateTimeImmutable::createFromFormat(
        format: 'Y-m-d', 
        datetime: $regex->replace(
            haystack: $date,
            replacement: static fn(array $matches) => sprintf(
                '%04d-%02d-%02d', 
                $matches['year'], 
                $monthNameToNumber[strtolower($matches['month'])] ?? 1,
            )
        );
    );
}

```