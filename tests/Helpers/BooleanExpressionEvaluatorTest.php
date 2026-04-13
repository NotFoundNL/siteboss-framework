<?php

use NotFound\Framework\Helpers\BooleanExpressionEvaluator;

// Literals
it('evaluates true', fn () => expect(BooleanExpressionEvaluator::evaluate('true'))->toBeTrue());
it('evaluates false', fn () => expect(BooleanExpressionEvaluator::evaluate('false'))->toBeFalse());

// NOT
it('negates true', fn () => expect(BooleanExpressionEvaluator::evaluate('!true'))->toBeFalse());
it('negates false', fn () => expect(BooleanExpressionEvaluator::evaluate('!false'))->toBeTrue());
it('double negation', fn () => expect(BooleanExpressionEvaluator::evaluate('!!true'))->toBeTrue());

// AND
it('true && true', fn () => expect(BooleanExpressionEvaluator::evaluate('true && true'))->toBeTrue());
it('true && false', fn () => expect(BooleanExpressionEvaluator::evaluate('true && false'))->toBeFalse());
it('false && true', fn () => expect(BooleanExpressionEvaluator::evaluate('false && true'))->toBeFalse());
it('false && false', fn () => expect(BooleanExpressionEvaluator::evaluate('false && false'))->toBeFalse());

// OR
it('true || true', fn () => expect(BooleanExpressionEvaluator::evaluate('true || true'))->toBeTrue());
it('true || false', fn () => expect(BooleanExpressionEvaluator::evaluate('true || false'))->toBeTrue());
it('false || true', fn () => expect(BooleanExpressionEvaluator::evaluate('false || true'))->toBeTrue());
it('false || false', fn () => expect(BooleanExpressionEvaluator::evaluate('false || false'))->toBeFalse());

// Operator precedence: && binds tighter than ||
it('false || true && false equals false || (true && false)', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('false || true && false'))->toBeFalse()
);
it('true || false && false equals true || (false && false)', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('true || false && false'))->toBeTrue()
);

// Parentheses override precedence
it('(false || true) && false', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('(false || true) && false'))->toBeFalse()
);
it('(false || true) && true', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('(false || true) && true'))->toBeTrue()
);

// NOT with parentheses
it('!(true && false)', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('!(true && false)'))->toBeTrue()
);
it('!(true || false)', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('!(true || false)'))->toBeFalse()
);

// Complex nested expressions (mirrors real checkRights usage)
it('(!false) && true', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('(!false) && true'))->toBeTrue()
);
it('!false || false', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('!false || false'))->toBeTrue()
);
it('(true && (false || true)) && !false', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('(true && (false || true)) && !false'))->toBeTrue()
);
it('(true && (false || false)) && !false', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('(true && (false || false)) && !false'))->toBeFalse()
);

// Whitespace tolerance
it('handles extra spaces', fn () =>
    expect(BooleanExpressionEvaluator::evaluate('true  &&  false'))->toBeFalse()
);
