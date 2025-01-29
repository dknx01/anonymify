<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/tests')
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'header_comment' => ['header' => <<<HEADER
Project: Anonymify
@copyright dknx01 (https://github.com/dknx01/anonymify)
HEADER,
        ],
        'modernize_strpos' => true,
        'php_unit_method_casing' => true,
        'general_phpdoc_annotation_remove' => ['annotations' => ['expectedDeprecation']], // one should use PHPUnit built-in method instead
    ])
    ->setFinder($finder)
;
