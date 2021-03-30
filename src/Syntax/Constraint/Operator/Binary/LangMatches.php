<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;

class LangMatches extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-add.
     */
    public function __construct(PlainLiteral $leftExpression, PlainLiteral $rightExpression)
    {
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('langMATCHES(%s,%s)', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}