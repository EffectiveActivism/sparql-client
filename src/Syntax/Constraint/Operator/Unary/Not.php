<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Not extends AbstractUnaryOperator implements UnaryOperatorInterface
{
    public function __construct(AbstractIri|AbstractLiteral|Variable $expression)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        return sprintf('! %s', $this->expression->serialize());
    }
}
