<?php

declare(strict_types=1);
use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use SlevomatCodingStandard\Sniffs\TypeHints\NullableTypeForNullDefaultValueSniff;

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
        NullableTypeForNullDefaultValueSniff::class,
    ],
    'config' => [
        LineLengthSniff::class => [
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
