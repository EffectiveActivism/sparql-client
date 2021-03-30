<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractUnaryOperator implements UnaryOperatorInterface
{
    protected TermInterface $expression;

    public function getExpression(): TermInterface
    {
        return $this->expression;
    }
}
