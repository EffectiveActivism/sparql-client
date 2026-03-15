<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate;

abstract class AbstractAggregateOperator implements AggregateInterface
{
    protected bool $isDistinct = false;

    public function distinct(bool $distinct = true): static
    {
        $this->isDistinct = $distinct;
        return $this;
    }
}
