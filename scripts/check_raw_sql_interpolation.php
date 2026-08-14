<?php

/**
 * Fails when raw-SQL helper calls interpolate a PHP variable inside the SQL
 * string. Query values must be supplied through bound parameters instead.
 *
 * This intentionally flags every direct interpolation occurrence, including
 * cases that also have a bindings array, because parameter binding cannot make
 * an already-interpolated SQL fragment safe.
 */

declare(strict_types=1);

$root = dirname(__DIR__);
$scanRoots = [$root . '/app', $root . '/scripts'];
$methodPattern = '(?:whereRaw|orWhereRaw|havingRaw|selectRaw|orderByRaw|groupByRaw|raw|statement|select|unprepared)';
$callPattern = '/(?:(?:DB|\\\\Illuminate\\\\Support\\\\Facades\\\\DB)::)?' . $methodPattern . '\\s*\\(\\s*([\'\"])(?:\\\\.|(?!\\1).)*?\\$[A-Za-z_][A-Za-z0-9_]*/s';
$violations = [];

foreach ($scanRoots as $scanRoot) {
    if (! is_dir($scanRoot)) {
        continue;
    }

    $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($scanRoot));
    foreach ($iterator as $file) {
        if (! $file->isFile() || $file->getExtension() !== 'php') {
            continue;
        }

        $path = $file->getPathname();
        if ($path === __FILE__) {
            continue;
        }

        $contents = file_get_contents($path);
        if ($contents === false) {
            fwrite(STDERR, "Unable to read {$path}.\n");
            exit(2);
        }

        if (preg_match_all($callPattern, $contents, $matches, PREG_OFFSET_CAPTURE)) {
            foreach ($matches[0] as $match) {
                $line = substr_count($contents, "\n", 0, $match[1]) + 1;
                $violations[] = str_replace($root . '/', '', $path) . ':' . $line;
            }
        }
    }
}

if ($violations !== []) {
    fwrite(STDERR, "Unsafe raw-SQL interpolation detected:\n");
    foreach ($violations as $violation) {
        fwrite(STDERR, " - {$violation}\n");
    }
    fwrite(STDERR, "Use a ? placeholder with a bindings array instead of interpolating PHP variables into SQL.\n");
    exit(1);
}

echo "Raw-SQL interpolation check passed.\n";
