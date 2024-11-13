<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\Symfony\Set\SymfonySetList;
use Rector\Set\ValueObject\SetList;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $rectorConfig->import(SymfonySetList::SYMFONY_54);
    $rectorConfig->import(SetList::PHP_81);
    $rectorConfig->import(SetList::CODE_QUALITY);
    $rectorConfig->import(SetList::TYPE_DECLARATION);
    $rectorConfig->import(SetList::DEAD_CODE);
    $rectorConfig->import(SetList::PRIVATIZATION);
    $rectorConfig->import(SetList::CODING_STYLE);

    // Link to PHPStan configuration
    $rectorConfig->phpstanConfig(__DIR__ . '/phpstan.neon');
};
