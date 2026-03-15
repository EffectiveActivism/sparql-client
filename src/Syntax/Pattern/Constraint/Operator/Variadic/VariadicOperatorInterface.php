<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;

interface VariadicOperatorInterface extends OperatorInterface
{
    public function getExpressions(): array;
}
