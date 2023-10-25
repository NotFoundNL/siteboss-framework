<?php

declare(strict_types=1);

return [
    'preset' => 'laravel',
    'exclude' => [
        //  'path/to/directory-or-file'
    ],
    'add' => [
            Classes::class => [
                ForbiddenFinalClasses::class,
            ],
    ],
    'remove' => [
        AlphabeticallySortedUsesSniff::class,
        DeclareStrictTypesSniff::class,
        DisallowMixedTypeHintSniff::class,
        ForbiddenDefineFunctions::class,
        ForbiddenNormalClasses::class,
        ForbiddenTraits::class,
        ParameterTypeHintSniff::class,
        PropertyTypeHintSniff::class,
        ReturnTypeHintSniff::class,
        UselessFunctionDocCommentSniff::class,
        // Pint will remove this, so don't enforce it
        SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff::class,
    ],
    'config' => [
        \PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff::class => [
            'lineLimit' => 120,
            'absoluteLineLimit' => 160,
        ],
        ForbiddenPrivateMethods::class => [
            'title' => 'The usage of private methods is not idiomatic in Laravel.',
        ],
    ],
    'requirements' => [
        'min-quality' => 90,
        'min-complexity' => 77,
        'min-architecture' => 90,
        'min-style' => 90,
    ],
];