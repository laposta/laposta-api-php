<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$baseDir = getenv('SCOPER_BASE_DIR') ?: getcwd() ?: __DIR__;
$vendorDir = getenv('SCOPER_VENDOR_DIR') ?: $baseDir . '/vendor';
$prefix = getenv('SCOPER_PREFIX') ?: 'LapostaApi\\Vendor';
$excludeNamespaces = getenv('SCOPER_EXCLUDE_NAMESPACES') ?: 'LapostaApi';

return [
    'prefix' => $prefix,
    'finders' => [
        Finder::create()->files()->in($baseDir . '/src'),
        Finder::create()->files()->in($vendorDir),
    ],
    'exclude-namespaces' => array_values(array_filter(array_map('trim', explode(',', $excludeNamespaces)))),
];
