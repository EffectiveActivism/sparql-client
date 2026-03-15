<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class BNodeOp implements OperatorInterface
{
    protected OperatorInterface|TermInterface|null $expression;

    public function __construct(OperatorInterface|TermInterface|null $expression = null)
    {
        $this->expression = $expression;
    }

    public function serialize(): string
    {
        if ($this->expression === null) {
            return 'BNODE()';
        }
        return sprintf('BNODE(%s)', $this->expression->serialize());
    }
}
