<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;

class Regex extends AbstractTrinaryOperator implements TrinaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/xpath-functions/#func-matches.
     */
    public function __construct(PlainLiteral $string, PlainLiteral $pattern, PlainLiteral $flags = null)
    {
        $this->leftExpression = $string;
        $this->middleExpression = $pattern;
        $this->rightExpression = $flags;
    }

    public function serialize(): string
    {
        return $this->rightExpression === null ?
            sprintf('REGEX(%s,%s)', $this->leftExpression->serialize(), $this->middleExpression->serialize()) :
            sprintf('REGEX(%s,%s,%s)', $this->leftExpression->serialize(), $this->middleExpression->serialize(), $this->rightExpression->serialize());
    }
}
