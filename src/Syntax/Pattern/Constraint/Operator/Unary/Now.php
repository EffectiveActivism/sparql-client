<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;

class Now implements OperatorInterface
{
    public function serialize(): string
    {
        return 'NOW()';
    }
}
