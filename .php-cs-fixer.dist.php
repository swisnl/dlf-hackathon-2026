<?php

$finder = PhpCsFixer\Finder::create()
    ->in(array_filter([
        __DIR__.'/app',
        __DIR__.'/bootstrap',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/lang',
        __DIR__.'/routes',
        __DIR__.'/tests',
    ], is_dir(...)))
    ->exclude([
        'cache',
    ]);

return Swis\PhpCsFixer\Config\Factory::create()
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache/.php-cs-fixer.cache')
    ->setFinder($finder);
