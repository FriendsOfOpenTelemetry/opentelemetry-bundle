<?php

use ShipMonk\ComposerDependencyAnalyser\Config\Configuration;
use ShipMonk\ComposerDependencyAnalyser\Config\ErrorType;

$config = new Configuration();

$composer = json_decode(file_get_contents(__DIR__.'/composer.json'), true);

return $config
    ->ignoreErrorsOnPackages(array_keys($composer['suggest']), [
        ErrorType::SHADOW_DEPENDENCY,
        ErrorType::DEV_DEPENDENCY_IN_PROD,
    ])
;
