<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractBinaryOperator implements BinaryOperatorInterface
{
    protected OperatorInterface|TermInterface $leftExpression;

    protected OperatorInterface|TermInterface $rightExpression;

    public function getLeftExpression(): OperatorInterface|TermInterface
    {
        return $this->leftExpression;
    }

    public function getRightExpression(): OperatorInterface|TermInterface
    {
        return $this->rightExpression;
    }
}
