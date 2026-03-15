<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Sum extends AbstractAggregateOperator implements AggregateInterface
{
    protected OperatorInterface|TermInterface $expression;

    public function __construct(OperatorInterface|TermInterface $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        if ($this->isDistinct) {
            return sprintf('SUM(DISTINCT %s)', $this->expression->serialize());
        }
        return sprintf('SUM(%s)', $this->expression->serialize());
    }
}
