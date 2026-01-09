<?php

declare(strict_types=1);

use Isolated\Symfony\Component\Finder\Finder;

$baseDir = getenv('SCOPER_BASE_DIR') ?: getcwd() ?: __DIR__;
$vendorDir = getenv('SCOPER_VENDOR_DIR') ?: $baseDir . '/vendor';

return [
    'prefix' => 'LapostaApi\\Vendor',
    'finders' => [
        Finder::create()->files()->in($baseDir . '/src'),
        Finder::create()->files()->in($vendorDir),
    ],
    'exclude-namespaces' => [
        'LapostaApi',
    ],
];
