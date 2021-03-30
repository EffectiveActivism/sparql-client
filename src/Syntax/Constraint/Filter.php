<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Constraint;

use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary\BinaryOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary\TrinaryOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\UnaryOperatorInterface;

class Filter implements ConstraintInterface
{
    protected OperatorInterface $operator;

    public function __construct(OperatorInterface $operator) {
        $this->operator = $operator;
    }

    public function __toString(): string
    {
        return sprintf('FILTER(%s)', $this->operator->serialize());
    }

    public function toArray(): array
    {
        $terms = [];
        if ($this->operator instanceof UnaryOperatorInterface) {
            $terms[] = $this->operator->getExpression();
        }
        elseif ($this->operator instanceof BinaryOperatorInterface) {
            $terms[] = $this->operator->getLeftExpression();
            $terms[] = $this->operator->getRightExpression();
        }
        elseif ($this->operator instanceof TrinaryOperatorInterface) {
            $terms[] = $this->operator->getLeftExpression();
            $terms[] = $this->operator->getMiddleExpression();
            if ($this->operator->getRightExpression() !== null) {
                $terms[] = $this->operator->getRightExpression();
            }
        }
        return $terms;
    }
}
