<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class SameTerm extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-add.
     */
    public function __construct(TermInterface $leftExpression, TermInterface $rightExpression)
    {
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('sameTERM(%s,%s)', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
