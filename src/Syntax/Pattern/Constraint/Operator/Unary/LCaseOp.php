<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class LCaseOp implements UnaryOperatorInterface
{
    protected OperatorInterface|TermInterface $expression;

    public function __construct(OperatorInterface|TermInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): OperatorInterface|TermInterface
    {
        return $this->expression;
    }

    public function serialize(): string
    {
        return sprintf('LCASE(%s)', $this->expression->serialize());
    }
}
