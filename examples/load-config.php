<?php

// Load config from config.php (which should be a copy of config-example.php)
$configFile = __DIR__ . '/config.php';

if (!file_exists($configFile)) {
    echo "Error: Configuration file config.php not found.\n";
    echo "Please copy config-example.php to config.php and fill in your API_KEY.\n";
    exit(1);
}

// Inject config values into $_ENV
$configValues = require $configFile;

if (is_array($configValues)) {
    foreach ($configValues as $key => $value) {
        putenv("$key=$value");
    }
} else {
    echo "Error: config.php did not return an array or is invalid.\n";
    exit(1);
}

// Ensure LP_EX_API_KEY is available and not the placeholder
if (!getenv('LP_EX_API_KEY')) {
    echo "Error: LP_EX_API_KEY is not set in config.php.\n";
    exit(1);
}
