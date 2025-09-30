<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    // Define the paths to your source code
    $rectorConfig->paths([
        __DIR__ . '/src',
    ]);

    // Apply sets for PHP upgrades
    $rectorConfig->sets([
        SetList::PHP_74,
        SetList::PHP_80,
        SetList::PHP_81,
        SetList::PHP_82,
        SetList::PHP_83,
    ]);
};