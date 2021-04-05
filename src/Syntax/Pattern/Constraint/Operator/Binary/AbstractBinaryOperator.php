<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractBinaryOperator implements BinaryOperatorInterface
{
    protected TermInterface $leftExpression;

    protected TermInterface $rightExpression;

    public function getLeftExpression(): TermInterface
    {
        return $this->leftExpression;
    }

    public function getRightExpression(): TermInterface
    {
        return $this->rightExpression;
    }
}
