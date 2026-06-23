<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractUnaryOperator implements UnaryOperatorInterface
{
    protected OperatorInterface|TermInterface $expression;

    public function getExpression(): OperatorInterface|TermInterface
    {
        return $this->expression;
    }
}
