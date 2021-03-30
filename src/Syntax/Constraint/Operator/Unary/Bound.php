<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Bound extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-bound.
     */
    public function __construct(Variable $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('BOUND(%s)', $this->expression->serialize());
    }
}
