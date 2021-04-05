<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;

class Lang extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-lang.
     */
    public function __construct(AbstractLiteral $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('LANG(%s)', $this->expression->serialize());
    }
}
