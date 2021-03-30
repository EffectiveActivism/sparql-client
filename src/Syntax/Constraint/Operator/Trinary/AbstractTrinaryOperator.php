<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractTrinaryOperator implements TrinaryOperatorInterface
{
    protected TermInterface $leftExpression;

    protected TermInterface $middleExpression;

    protected TermInterface|null $rightExpression;

    /**
     * Getters.
     */

    public function getLeftExpression(): TermInterface
    {
        return $this->leftExpression;
    }

    public function getMiddleExpression(): TermInterface
    {
        return $this->middleExpression;
    }

    public function getRightExpression(): TermInterface|null
    {
        return $this->rightExpression;
    }
}
