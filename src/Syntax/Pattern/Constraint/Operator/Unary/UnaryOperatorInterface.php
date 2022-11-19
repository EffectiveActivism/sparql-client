<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface UnaryOperatorInterface extends OperatorInterface
{
    public function getExpression(): OperatorInterface|TermInterface;
}
