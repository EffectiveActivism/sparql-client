<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class SubStr extends AbstractTrinaryOperator implements TrinaryOperatorInterface
{
    public function __construct(OperatorInterface|TermInterface $str, OperatorInterface|TermInterface $startingLoc, OperatorInterface|TermInterface|null $length = null)
    {
        $this->leftExpression = $str;
        $this->middleExpression = $startingLoc;
        $this->rightExpression = $length;
    }

    public function serialize(): string
    {
        return $this->rightExpression === null ?
            sprintf('SUBSTR(%s,%s)', $this->leftExpression->serialize(), $this->middleExpression->serialize()) :
            sprintf('SUBSTR(%s,%s,%s)', $this->leftExpression->serialize(), $this->middleExpression->serialize(), $this->rightExpression->serialize());
    }
}
