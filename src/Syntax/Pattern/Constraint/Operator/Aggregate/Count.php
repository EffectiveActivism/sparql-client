<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Count extends AbstractAggregateOperator implements AggregateInterface
{
    protected OperatorInterface|TermInterface|null $expression;

    public function __construct(OperatorInterface|TermInterface|null $expression = null)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        if ($this->expression === null) {
            return 'COUNT(*)';
        }
        if ($this->isDistinct) {
            return sprintf('COUNT(DISTINCT %s)', $this->expression->serialize());
        }
        return sprintf('COUNT(%s)', $this->expression->serialize());
    }
}
