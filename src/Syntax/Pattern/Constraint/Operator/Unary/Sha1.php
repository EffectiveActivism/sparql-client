<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Sha1 implements UnaryOperatorInterface
{
    protected OperatorInterface|TermInterface $expression;

    public function __construct(OperatorInterface|TermInterface $expression)
    {
        $this->expression = $expression;
    }

    public function getExpression(): OperatorInterface|TermInterface
    {
        return $this->expression;
    }

    public function serialize(): string
    {
        return sprintf('SHA1(%s)', $this->expression->serialize());
    }
}
