<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;

class Positive extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#operandDataTypes.
     */
    public function __construct(AbstractLiteral $expression)
    {
        if ($expression->getType() !== 'xsd:integer') {
            throw new SparQlException(sprintf('Expression "%s" is not of type numeric', $expression->getRawValue()));
        }
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('+ %s', $this->expression->serialize());
    }
}
