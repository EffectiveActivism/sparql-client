<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Multiply extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see http://www.w3.org/TR/xpath-functions/#func-numeric-multiply.
     *
     * @throws SparQlException
     */
    public function __construct(OperatorInterface|TermInterface $leftExpression, OperatorInterface|TermInterface $rightExpression)
    {
        $this->assertNumericOperands($leftExpression, $rightExpression);
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('%s * %s', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
