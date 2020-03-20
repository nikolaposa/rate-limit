<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
;

return PhpCsFixer\Config::create()
    ->setRules([
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'declare_strict_types' => true,
        'return_type_declaration' => ['space_before' => 'none'],
        'phpdoc_line_span' => ['property' => 'single'],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder)
;
