<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class StrLang extends AbstractBinaryOperator implements BinaryOperatorInterface
{
    public function __construct(OperatorInterface|TermInterface $leftExpression, OperatorInterface|TermInterface $rightExpression)
    {
        $this->leftExpression = $leftExpression;
        $this->rightExpression = $rightExpression;
    }

    public function serialize(): string
    {
        return sprintf('STRLANG(%s,%s)', $this->leftExpression->serialize(), $this->rightExpression->serialize());
    }
}
