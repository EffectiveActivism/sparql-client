<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class In extends AbstractVariadicOperator implements VariadicOperatorInterface
{
    protected OperatorInterface|TermInterface $subject;

    public function __construct(OperatorInterface|TermInterface $subject, OperatorInterface|TermInterface ...$expressions)
    {
        $this->subject = $subject;
        $this->expressions = $expressions;
    }

    public function getExpressions(): array
    {
        return array_merge([$this->subject], $this->expressions);
    }

    public function serialize(): string
    {
        $parts = array_map(fn($e) => $e->serialize(), $this->expressions);
        return sprintf('%s IN (%s)', $this->subject->serialize(), implode(', ', $parts));
    }
}
