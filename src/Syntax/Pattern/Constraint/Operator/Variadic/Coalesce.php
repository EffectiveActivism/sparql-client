<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Coalesce extends AbstractVariadicOperator implements VariadicOperatorInterface
{
    public function __construct(OperatorInterface|TermInterface ...$expressions)
    {
        $this->expressions = $expressions;
    }

    public function serialize(): string
    {
        $parts = array_map(fn($e) => $e->serialize(), $this->expressions);
        return sprintf('COALESCE(%s)', implode(', ', $parts));
    }
}
