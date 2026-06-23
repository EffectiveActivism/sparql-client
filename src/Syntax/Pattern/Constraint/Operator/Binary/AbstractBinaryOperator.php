<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * The XSD numeric type hierarchy that the arithmetic operators accept as
     * literal operands.
     *
     * @see https://www.w3.org/TR/sparql11-query/#operandDataTypes
     */
    protected const NUMERIC_TYPES = ['xsd:integer', 'xsd:decimal', 'xsd:float', 'xsd:double'];

    protected OperatorInterface|TermInterface $leftExpression;

    protected OperatorInterface|TermInterface $rightExpression;

    /**
     * Rejects literal operands that are not numeric. Non-literal operands
     * (variables, nested operators) are left to the endpoint to type-check at
     * evaluation time.
     *
     * @throws SparQlException
     */
    protected function assertNumericOperands(OperatorInterface|TermInterface $leftExpression, OperatorInterface|TermInterface $rightExpression): void
    {
        foreach ([$leftExpression, $rightExpression] as $expression) {
            if ($expression instanceof AbstractLiteral && !in_array($expression->getType(), self::NUMERIC_TYPES, true)) {
                // serialize() rather than getRawValue(): an operand may be an
                // OperatorInterface, which has no getRawValue().
                throw new SparQlException(sprintf('Type error: "%s" and "%s" must be numeric or a variable', $leftExpression->serialize(), $rightExpression->serialize()));
            }
        }
    }

    public function getLeftExpression(): OperatorInterface|TermInterface
    {
        return $this->leftExpression;
    }

    public function getRightExpression(): OperatorInterface|TermInterface
    {
        return $this->rightExpression;
    }
}
