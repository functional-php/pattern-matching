<?php

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = Finder::create()
    ->exclude(['vendor', 'cache', 'bin'])
    ->in(__DIR__);

$config = new Config;

return $config
    ->setRules([
        '@PSR12' => true
    ])
    ->setFinder($finder);
