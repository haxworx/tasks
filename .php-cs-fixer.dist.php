<?php

declare(strict_types=1);

if (!file_exists(__DIR__.'/src')) {
    exit(0);
}

$config = new PhpCsFixer\Config();

return $config
    ->setRules([
        '@PER' => true,
        '@DoctrineAnnotation' => true,
        '@PSR1' => true,
        '@PSR12' => true,
        '@PSR2' => true,
        '@PhpCsFixer' => true,
        '@Symfony' => true,
        'concat_space' => false,
        'protected_to_private' => false,
        'native_constant_invocation' => ['strict' => false],
        'nullable_type_declaration_for_default_null_value' => ['use_nullable_type_declaration' => false],
        'no_superfluous_phpdoc_tags' => ['remove_inheritdoc' => true],
        'modernize_strpos' => true,
        'get_class_to_class_keyword' => true,
        'declare_strict_types' => true,
        '@PHP82Migration' => true,
    ])
    ->setRiskyAllowed(true)
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->in([__DIR__.'/src', __DIR__.'/tests'])
            ->append([__FILE__])
    )
    ->setCacheFile('.php-cs-fixer.cache')
;
