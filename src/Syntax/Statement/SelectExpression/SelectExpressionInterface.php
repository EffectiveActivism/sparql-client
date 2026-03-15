<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

interface SelectExpressionInterface
{
    public function serialize(): string;

    public function getVariable(): Variable;

    public function getExpression(): OperatorInterface;
}
