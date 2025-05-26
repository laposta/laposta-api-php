<?php

spl_autoload_register(function (string $class): void {
    // Configuration for different namespaces
    $namespaces = [
        'LapostaApi\\' => __DIR__ . '/../src/',
        'Psr\\Http\\' => __DIR__ . '/Psr/Http/',
    ];

    // Check each namespace configuration
    foreach ($namespaces as $prefix => $baseDir) {
        // If the class starts with this namespace prefix
        if (str_starts_with($class, $prefix)) {
            // Get the relative class path
            $relativeClass = substr($class, strlen($prefix));

            // Build the full file path
            $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

            // Load the file if it exists
            if (file_exists($file)) {
                require $file;
                return; // Stop after loading the file
            }
        }
    }
});
