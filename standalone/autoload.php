<?php

/**
 * PSR-4 compatible autoloader for the Laposta API library.
 *
 * This autoloader maps namespace prefixes to directory structures following
 * the PSR-4 standard (https://www.php-fig.org/psr/psr-4/).
 * It handles both the main LapostaApi namespace and the PSR HTTP interfaces.
 *
 * Note: This autoloader is only needed when using the library without Composer.
 * If you're using Composer, its autoloader will handle these dependencies automatically.
 */

spl_autoload_register(function (string $class): void {
    // Configuration for different namespaces and their base directories
    $namespaces = [
        'LapostaApi\\' => __DIR__ . '/../src/',
        'Psr\\Http\\' => __DIR__ . '/Psr/Http/',
    ];

    // Check each namespace configuration
    foreach ($namespaces as $prefix => $baseDir) {
        // If the class starts with this namespace prefix
        if (str_starts_with($class, $prefix)) {
            // Get the relative class path by removing the namespace prefix
            $relativeClass = substr($class, strlen($prefix));

            // Build the full file path by converting namespace separators to directory separators
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            // Load the file if it exists
            if (file_exists($file)) {
                require $file;
                return;
            }
        }
    }
});
