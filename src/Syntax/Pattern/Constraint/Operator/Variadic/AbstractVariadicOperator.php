<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractVariadicOperator implements VariadicOperatorInterface
{
    protected array $expressions;

    public function getExpressions(): array
    {
        return $this->expressions;
    }
}
