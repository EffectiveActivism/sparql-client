<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use InvalidArgumentException;

class LessThan extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-less-than.
     */
    public function __construct(AbstractLiteral $leftExpression, AbstractLiteral $rightExpression)
    {
        if ($leftExpression->getType() !== $rightExpression->getType()) {
            throw new InvalidArgumentException(sprintf('Type mismatch: "%s" and "%s" must be same type', $leftExpression->getRawValue(), $rightExpression->getRawValue()));
        }
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('%s < %s', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
