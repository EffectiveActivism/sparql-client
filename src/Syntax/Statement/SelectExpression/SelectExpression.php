<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class SelectExpression implements SelectExpressionInterface
{
    protected OperatorInterface $expression;

    protected Variable $variable;

    public function __construct(OperatorInterface $expression, Variable $variable)
    {
        $this->expression = $expression;
        $this->variable = $variable;
    }

    public function serialize(): string
    {
        return sprintf('( %s AS %s )', $this->expression->serialize(), $this->variable->serialize());
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function getExpression(): OperatorInterface
    {
        return $this->expression;
    }
}
