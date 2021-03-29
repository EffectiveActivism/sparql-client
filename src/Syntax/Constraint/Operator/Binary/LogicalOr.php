<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class LogicalOr extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    /**
     * @see https://www.w3.org/TR/rdf-sparql-query/#func-logical-or.
     */
    public function __construct(TermInterface $leftExpression, TermInterface $rightExpression)
    {
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;

    }

    public function serialize(): string
    {
        return sprintf('%s || %s', $this->leftExpression, $this->rightExpression);
    }
}
