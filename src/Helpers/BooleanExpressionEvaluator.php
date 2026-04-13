<?php

namespace NotFound\Framework\Helpers;

class BooleanExpressionEvaluator
{
    /**
     * Evaluate a boolean expression string containing only "true", "false",
     * "&&", "||", "!", "(", ")" and whitespace.
     */
    public static function evaluate(string $expression): bool
    {
        $tokens = self::tokenize($expression);
        $pos = 0;

        return self::parseOr($tokens, $pos);
    }

    private static function tokenize(string $expression): array
    {
        $tokens = [];
        $i = 0;
        $len = strlen($expression);
        while ($i < $len) {
            if ($expression[$i] === ' ') {
                $i++;
            } elseif ($i + 1 < $len && $expression[$i] === '&' && $expression[$i + 1] === '&') {
                $tokens[] = '&&';
                $i += 2;
            } elseif ($i + 1 < $len && $expression[$i] === '|' && $expression[$i + 1] === '|') {
                $tokens[] = '||';
                $i += 2;
            } elseif ($expression[$i] === '!') {
                $tokens[] = '!';
                $i++;
            } elseif ($expression[$i] === '(') {
                $tokens[] = '(';
                $i++;
            } elseif ($expression[$i] === ')') {
                $tokens[] = ')';
                $i++;
            } elseif (substr($expression, $i, 4) === 'true') {
                $tokens[] = 'true';
                $i += 4;
            } elseif (substr($expression, $i, 5) === 'false') {
                $tokens[] = 'false';
                $i += 5;
            } else {
                $i++;
            }
        }

        return $tokens;
    }

    private static function parseOr(array $tokens, int &$pos): bool
    {
        $left = self::parseAnd($tokens, $pos);
        while (isset($tokens[$pos]) && $tokens[$pos] === '||') {
            $pos++;
            $right = self::parseAnd($tokens, $pos);
            $left = $left || $right;
        }

        return $left;
    }

    private static function parseAnd(array $tokens, int &$pos): bool
    {
        $left = self::parseNot($tokens, $pos);
        while (isset($tokens[$pos]) && $tokens[$pos] === '&&') {
            $pos++;
            $right = self::parseNot($tokens, $pos);
            $left = $left && $right;
        }

        return $left;
    }

    private static function parseNot(array $tokens, int &$pos): bool
    {
        if (isset($tokens[$pos]) && $tokens[$pos] === '!') {
            $pos++;

            return ! self::parseNot($tokens, $pos);
        }

        return self::parsePrimary($tokens, $pos);
    }

    private static function parsePrimary(array $tokens, int &$pos): bool
    {
        if (! isset($tokens[$pos])) {
            return false;
        }
        if ($tokens[$pos] === '(') {
            $pos++;
            $value = self::parseOr($tokens, $pos);
            if (isset($tokens[$pos]) && $tokens[$pos] === ')') {
                $pos++;
            }

            return $value;
        }
        if ($tokens[$pos] === 'true') {
            $pos++;

            return true;
        }
        if ($tokens[$pos] === 'false') {
            $pos++;

            return false;
        }
        $pos++;

        return false;
    }
}
