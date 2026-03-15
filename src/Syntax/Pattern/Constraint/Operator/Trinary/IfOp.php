<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class IfOp extends AbstractTrinaryOperator implements TrinaryOperatorInterface
{
    public function __construct(OperatorInterface|TermInterface $condition, OperatorInterface|TermInterface $thenExpr, OperatorInterface|TermInterface $elseExpr)
    {
        $this->leftExpression = $condition;
        $this->middleExpression = $thenExpr;
        $this->rightExpression = $elseExpr;
    }

    public function serialize(): string
    {
        return sprintf('IF(%s,%s,%s)', $this->leftExpression->serialize(), $this->middleExpression->serialize(), $this->rightExpression->serialize());
    }
}
