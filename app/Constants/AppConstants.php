<?php

namespace App\Constants;

class AppConstants
{
    public const DEBUG_MODE = true;
    public const ADMIN_ROLE = 'admin';
    public const USER_ROLE = 'user';
    public const GUEST_ROLE = 'guest';
    public const ACTIVE_STATUS = 1;
    public const INACTIVE_STATUS = 0;
    public const PAGINATION_LIMIT = 15;
    public const API_VERSION = 'v1';

    /**
     * Legacy constant kept for backward compatibility (e.g. Notification classes
     * that reference it as a static property default). Do not use in new code —
     * call imageBase() / imageUrl() instead.
     */
    public const DOMAIN = '';

    /**
     * The storage sub-path appended to APP_URL when no IMAGE_BASE_URL is set.
     * Changing IMAGE_PATH here or setting IMAGE_BASE_URL in .env is the only
     * place you need to touch to redirect all image loading to a different server.
     */
    public const IMAGE_PATH = '/storage';

    // ──────────────────────────────────────────────────────────────────────────
    // Domain helpers
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * The current app origin (no trailing slash).
     * Reads APP_URL from config so it always matches the running environment.
     */
    public static function domain(): string
    {
        return rtrim(config('app.url', ''), '/');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Image helpers  — single source of truth for ALL image URLs
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Base URL for stored images, with a trailing slash.
     *
     * Priority:
     *   1. IMAGE_BASE_URL env var  →  use it as-is (point to CDN / original server)
     *   2. Fallback                →  APP_URL + IMAGE_PATH  (local /storage)
     *
     * To redirect all images to an external server simply set:
     *   IMAGE_BASE_URL=https://old-server.com/storage
     * in your .env / Replit Secrets — no code changes needed anywhere.
     */
    public static function imageBase(): string
    {
        $override = config('app.image_base_url');
        if ($override) {
            return rtrim($override, '/') . '/';
        }
        $domain = static::domain();
        $host = strtolower((string) (parse_url($domain, PHP_URL_HOST) ?? ''));

        // A local APP_URL is useful for the server, but must never be emitted
        // into public HTML. Use a same-origin relative storage path instead so
        // mobile browsers do not attempt a Local Network Access request.
        if ($host && static::isLocalNetworkHost($host)) {
            return rtrim(static::IMAGE_PATH, '/') . '/';
        }

        return rtrim($domain . static::IMAGE_PATH, '/') . '/';
    }

    /**
     * Build a full absolute URL for a single image path stored in the database.
     * Returns null when $path is empty / null.
     *
     * Usage:
     *   AppConstants::imageUrl('products/other_images/foo.jpg')
     *   // → https://your-domain.com/storage/products/other_images/foo.jpg
     */
    public static function imageUrl(?string $path): ?string
    {
        if (!$path || trim($path) === '' || $path === 'empty') {
            return null;
        }

        $path = trim($path);
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $normalizedUrl = static::normalizeLegacyLocalUrl($path);

            if (str_starts_with($normalizedUrl, '/storage/')) {
                return static::mediaPathExists($normalizedUrl) ? $normalizedUrl : null;
            }

            // Imported timeline/configuration data can contain an absolute URL for
            // this application's own `/storage/...` path. Treat it as managed
            // media—not as an arbitrary third-party URL—so missing files do not
            // remain in customer HTML merely because the hostname is public.
            $urlParts = parse_url($normalizedUrl);
            $urlHost = strtolower((string) ($urlParts['host'] ?? ''));
            $appHost = strtolower((string) (parse_url(static::domain(), PHP_URL_HOST) ?? ''));
            $urlPath = (string) ($urlParts['path'] ?? '');
            if ($urlHost !== '' && $urlHost === $appHost && str_starts_with($urlPath, '/storage/')) {
                $query = isset($urlParts['query']) ? '?' . $urlParts['query'] : '';

                return static::imageUrl($urlPath . $query);
            }

            return $normalizedUrl;
        }

        $normalizedPath = ltrim(str_replace('\\', '/', $path), '/');
        // Timeline and legacy customer configuration may store a same-origin
        // `/storage/...` URL instead of a disk-relative path. Normalise it before
        // checking the configured disk so it neither becomes `storage/storage/...`
        // nor emits a broken customer-facing request.
        $normalizedPath = preg_replace('/^storage\//', '', $normalizedPath);

        if (! static::mediaPathExists($normalizedPath)) {
            return null;
        }

        // On object storage, let Laravel generate the native disk URL unless an
        // explicit public CDN base was configured. Existing local media keeps the
        // same stable `/storage/...` URL used by the storefront today.
        if (! config('app.image_base_url') && static::mediaDisk() !== 'public') {
            return \Illuminate\Support\Facades\Storage::disk(static::mediaDisk())->url($normalizedPath);
        }

        return static::imageBase() . $normalizedPath;
    }

    /** Determine whether a configured object-storage or legacy public-disk path exists. */
    private static function mediaPathExists(string $path): bool
    {
        $storagePath = preg_replace('/^(?:\/?storage\/)/', '', strtok($path, '?'));
        if ($storagePath === '') {
            return false;
        }

        foreach (array_unique([static::mediaDisk(), 'public']) as $disk) {
            if (\Illuminate\Support\Facades\Storage::disk($disk)->exists($storagePath)) {
                return true;
            }
        }

        return false;
    }

    /** The configured production filesystem disk, with public storage as a safe local fallback. */
    private static function mediaDisk(): string
    {
        return (string) config('filesystems.default', 'public');
    }

    /**
     * Turn an old localhost/private-network image URL into a same-origin path.
     *
     * Product and store assets imported from local development can contain URLs
     * such as http://127.0.0.1:5000/storage/… . Loading those from the public
     * storefront causes Chrome on Android to request Local Network Access.
     * Storage is served by this application, so retaining only the path keeps
     * the request on the current public origin and avoids that permission.
     */
    private static function normalizeLegacyLocalUrl(string $url): string
    {
        $parts = parse_url($url);
        $host = strtolower((string) ($parts['host'] ?? ''));

        if (!$host || !static::isLocalNetworkHost($host)) {
            return $url;
        }

        $path = '/' . ltrim((string) ($parts['path'] ?? '/'), '/');
        $query = isset($parts['query']) ? '?' . $parts['query'] : '';

        return $path . $query;
    }

    /** Determine whether a URL host is loopback or a private-network address. */
    private static function isLocalNetworkHost(string $host): bool
    {
        if (in_array($host, ['localhost', '::1'], true) || str_ends_with($host, '.localhost')) {
            return true;
        }

        return $host === '127.0.0.1'
            || str_starts_with($host, '127.')
            || str_starts_with($host, '10.')
            || str_starts_with($host, '192.168.')
            || (bool) preg_match('/^172\\.(1[6-9]|2[0-9]|3[0-1])\\./', $host);
    }

    /**
     * Extract the best thumbnail URL from a product's raw `images` JSON column.
     *
     * The column is stored as:
     *   {"thumbnail":"…", "other_images":[…], "natural_images":[…]}
     *
     * Falls back to first other_image, then first natural_image.
     */
    public static function productThumbnailUrl(mixed $imagesRaw): ?string
    {
        if (!$imagesRaw) return null;
        $imgs = is_string($imagesRaw)
            ? (json_decode($imagesRaw, true) ?? json_decode(stripslashes($imagesRaw), true) ?? [])
            : (array) $imagesRaw;

        $paths = array_merge(
            [$imgs['thumbnail'] ?? null],
            array_values((array) ($imgs['other_images'] ?? [])),
            array_values((array) ($imgs['natural_images'] ?? [])),
        );

        foreach ($paths as $path) {
            $url = static::imageUrl(is_string($path) ? $path : null);
            if ($url) {
                return $url;
            }
        }

        return null;
    }

    /**
     * Build full gallery URLs from a product's raw `images` JSON column.
     * Returns a flat array of absolute URLs (other_images + natural_images).
     *
     * @return string[]
     */
    public static function productGalleryUrls(mixed $imagesRaw): array
    {
        if (!$imagesRaw) return [];
        $imgs = is_string($imagesRaw)
            ? (json_decode($imagesRaw, true) ?? [])
            : (array) $imagesRaw;

        $paths = array_merge(
            (array) ($imgs['other_images']  ?? []),
            (array) ($imgs['natural_images'] ?? [])
        );

        return array_values(array_filter(array_map(
            fn($p) => static::imageUrl($p),
            $paths
        )));
    }
}
