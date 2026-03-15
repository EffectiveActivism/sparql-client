<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Concat extends AbstractVariadicOperator implements VariadicOperatorInterface
{
    /**
     * @throws SparQlException
     */
    public function __construct(OperatorInterface|TermInterface ...$expressions)
    {
        if (count($expressions) === 0) {
            throw new SparQlException('CONCAT requires at least one expression');
        }
        $this->expressions = $expressions;
    }

    public function serialize(): string
    {
        $parts = array_map(fn($e) => $e->serialize(), $this->expressions);
        return sprintf('CONCAT(%s)', implode(', ', $parts));
    }
}
