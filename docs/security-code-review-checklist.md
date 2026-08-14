# Security Code Review Checklist

## Database query safety

Changes that create or modify database queries must preserve parameter binding for all values. Reviewers should reject direct request-variable interpolation into SQL strings, including raw-query helpers and native PDO/driver calls. Use prepared statements, `?` or named placeholders, and bound values instead.

Dynamic SQL identifiers cannot be bound by database drivers. For customer-controlled sorting, filtering fields, table names, or column names, resolve the requested value through a fixed allowlist before it reaches the query. The approved pattern is a `match`, `switch`, or validation rule that maps input to literal, known-safe columns and directions.

```php
$column = match ($request->input('sort')) {
    'price_asc', 'price_desc' => 'price',
    default => 'created_at',
};

$direction = $request->input('sort') === 'price_asc' ? 'asc' : 'desc';
$query->orderBy($column, $direction);
```

Do not pass a raw request value into `orderBy()`, `orderByRaw()`, `whereRaw()`, `selectRaw()`, or a native database `query()`/`exec()` call. When raw SQL is unavoidable, run `php scripts/check_raw_sql_interpolation.php` locally and ensure the GitHub Actions raw-SQL safety check passes.

## Error handling

Production HTTP responses must contain generic database-error messages only. Log the underlying exception server-side with contextual, non-secret metadata; never serialize raw PDO, PostgreSQL, or SQL syntax errors to customers.
