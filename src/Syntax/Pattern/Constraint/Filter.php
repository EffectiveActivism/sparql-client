<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\AggregateInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\BinaryOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary\TrinaryOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\UnaryOperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\VariadicOperatorInterface;

class Filter implements ConstraintInterface
{
    protected OperatorInterface $operator;

    /**
     * @throws SparQlException
     */
    public function __construct(OperatorInterface $operator) {
        if ($operator instanceof AggregateInterface) {
            throw new SparQlException('Aggregates cannot be used in FILTER clauses');
        }
        $this->operator = $operator;
    }

    public function serialize(): string
    {
        return sprintf('FILTER(%s)', $this->operator->serialize());
    }

    public function toArray(): array
    {
        return $this->getTerms();
    }

    public function getTerms(): array
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
        elseif ($this->operator instanceof VariadicOperatorInterface) {
            foreach ($this->operator->getExpressions() as $expr) {
                $terms[] = $expr;
            }
        }
        return $terms;
    }
}
