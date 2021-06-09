<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class GreaterThan extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-greater-than.
     */
    public function __construct(AbstractLiteral|Variable $leftExpression, AbstractLiteral|Variable $rightExpression)
    {
        if (
            $leftExpression instanceof AbstractLiteral &&
            $rightExpression instanceof AbstractLiteral &&
            $leftExpression->getType() !== $rightExpression->getType()
        ) {
            throw new SparQlException(sprintf('Type mismatch: "%s" and "%s" must be same type or a variable', $leftExpression->getRawValue(), $rightExpression->getRawValue()));
        }
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('%s > %s', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
