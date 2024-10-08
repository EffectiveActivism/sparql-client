<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Datatype extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-datatype.
     */
    public function __construct(AbstractLiteral|Variable $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('DATATYPE(%s)', $this->expression->serialize());
    }
}
