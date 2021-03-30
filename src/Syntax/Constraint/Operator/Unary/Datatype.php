<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;

class Datatype extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-datatype.
     */
    public function __construct(AbstractLiteral $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('DATATYPE(%s)', $this->expression->serialize());
    }
}
