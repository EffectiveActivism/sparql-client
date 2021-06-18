<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Order;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Desc implements OrderModifierInterface
{
    protected Variable|OperatorInterface $expression;

    public function __construct(Variable|OperatorInterface $expression) {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('DESC( %s )', $this->expression->serialize());
    }
}
