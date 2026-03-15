<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;

interface AggregateInterface extends OperatorInterface
{
    public function distinct(bool $distinct = true): static;
}
