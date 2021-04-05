<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class IsUri extends AbstractUnaryOperator implements UnaryOperatorInterface
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
        return sprintf('IsURI(%s)', $this->expression->serialize());
    }
}
