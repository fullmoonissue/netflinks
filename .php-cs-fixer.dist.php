<?php

declare(strict_types=1);

return (new PhpCsFixer\Config())
    ->setRules(
        [
            '@PSR1' => true,
            '@PSR2' => true,
            '@Symfony' => true,
            'array_syntax' => ['syntax' => 'short'],
            'declare_strict_types' => true,
            'global_namespace_import' => [
                'import_classes' => true,
            ],
            'php_unit_method_casing' => ['case' => 'snake_case'],
            'phpdoc_annotation_without_dot' => false,
            'phpdoc_summary' => false,
            'single_line_throw' => false,
            'trailing_comma_in_multiline' => [
                'elements' => [
                    'arrays',
                    'parameters',
                ],
            ],
        ]
    )
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->name('*.php')
            ->in([__DIR__.'/config'])
            ->in([__DIR__.'/src'])
            ->in([__DIR__.'/tests'])
            ->append([__DIR__.'/.php-cs-fixer.dist.php'])
            ->append([__DIR__.'/rector.php'])
    );
