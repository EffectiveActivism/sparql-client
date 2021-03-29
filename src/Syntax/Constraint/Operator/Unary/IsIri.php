<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class IsIri extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-isIRI.
     */
    public function __construct(TermInterface $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('IsIRI(%s)', $this->expression->serialize());
    }
}
