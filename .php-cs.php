<?php

$rules = [
    '@PSR1' => true,
    '@PSR2' => true,
    '@Symfony' => true,
    '@PHP74Migration' => true,
    '@PHP74Migration:risky' => true,

    // Rewrites
    'concat_space' => ['spacing' => 'one'],
    'declare_strict_types' => false,
    'cast_spaces' => ['space' => 'none'],
    'unary_operator_spaces' => true,
    'function_declaration' => [
        'closure_function_spacing' => 'none',
    ],
    'increment_style' => false,
    //'protected_to_private' => false, // needs more discussion
    'single_space_after_construct' => false,
    'no_alternative_syntax' => false, // used in views
    'php_unit_method_casing' => false, // some tests does have different codestyle
    'echo_tag_syntax' => [
        'format' => 'short',
        'shorten_simple_statements_only' => false,
    ],
    'use_arrow_functions' => false, // disabled for a while to reduce diff

    // PhpCsFixer
    'array_syntax' => ['syntax' => 'short'],
    'array_indentation' => true,
    'combine_consecutive_unsets' => true,
    'compact_nullable_typehint' => true,
    'heredoc_to_nowdoc' => true,
    'no_useless_else' => true,
    'no_useless_return' => true,
    'no_superfluous_elseif' => true,

    // Extensions
    'blank_line_before_statement' => [
        'statements' => ['return']
    ],
    'linebreak_after_opening_tag' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'ordered_class_elements' => [ // needs more discussion
        'order' => [
            'use_trait',

            'constant_public',
            'constant_protected',
            'constant_private',

            'property_public_static',
            'property_protected_static',
            'property_private_static',

            'property_public',
            'property_protected',
            'property_private',

            'construct',
            'destruct',
            'magic',
            'phpunit',

            'method',
        ],
    ],
    'ordered_imports' => [
        'imports_order' => [
            'class',
            'function',
            'const',
        ],
    ],

    // Risky
    'dir_constant' => true,
    'ereg_to_preg' => true,
    'function_to_constant' => true,
    //'mb_str_functions' => true, // needs more discussion
    'modernize_types_casting' => true,
    'native_function_invocation' => [
        'include' => ['@compiler_optimized'],
        'scope' => 'namespaced',
        'strict' => true,
    ],
    'no_alias_functions' => true,
    'no_php4_constructor' => true,
    'no_unreachable_default_argument_value' => true,
    'psr_autoloading' => true,
    'self_accessor' => true,
    'strict_param' => true,
    'is_null' => true,
    'standardize_increment' => false,

    // PHPUnit risky
    'php_unit_construct' => true,
    'php_unit_dedicate_assert' => true,
    //'php_unit_strict' => true, // needs more attention

    // Docblocks & Comments
    'no_superfluous_phpdoc_tags' => false, // our project and IDEs are not ready
    'phpdoc_add_missing_param_annotation' => true,
    //'phpdoc_inline_tag' => true, // see https://github.com/yiisoft/yii2/issues/11635
    'phpdoc_to_comment' => false, // breaks phpdoc for define('CONSTANT', $value);
    'phpdoc_align' => ['align' => 'left'],
    'phpdoc_var_without_name' => false, // breaks param declaration in closures
    'phpdoc_separation' => false, // breaks Yii2 relations declarations separation from properties
];

// Directories to not scan
$excludeDirs = [
    'vendor/',
];

// Files to not scan
$excludeFiles = [
];

// Create a new Symfony Finder instance
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->sortByModifiedTime()
    ->exclude($excludeDirs)
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->filter(function(\SplFileInfo $file) use ($excludeFiles) {
        return !in_array($file->getRelativePathName(), $excludeFiles, true);
    });

$config = new PhpCsFixer\Config();

return $config
    ->setIndent('    ')
    ->setRules($rules)
    ->setFinder($finder)
    //->setUsingCache(false)
    ->setRiskyAllowed(true);
