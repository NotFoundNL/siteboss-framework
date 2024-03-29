<?php

declare(strict_types=1);

return [
    'preset' => 'laravel',
    'exclude' => [
    ],
    'add' => [
        // ForbiddenPrivateMethods::class => [
        //     'title' => 'The usage of private methods is not idiomatic in Laravel.',
        // ],
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
    ],
    'requirements' => [
        'min-quality' => 59,
        'min-complexity' => 60,
        'min-architecture' => 58,
        'min-style' => 86,
    ],
];
