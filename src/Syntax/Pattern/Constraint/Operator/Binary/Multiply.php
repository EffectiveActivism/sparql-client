<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;

class Multiply extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-multiply.
     */
    public function __construct(AbstractLiteral $leftExpression, AbstractLiteral $rightExpression)
    {
        if (
            $leftExpression->getType() !== 'xsd:integer' ||
            $rightExpression->getType() !== 'xsd:integer'
        ) {
            throw new SparQlException(sprintf('Type error: "%s" and "%s" must be of type xsd:integer', $leftExpression->getRawValue(), $rightExpression->getRawValue()));
        }
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('%s * %s', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
