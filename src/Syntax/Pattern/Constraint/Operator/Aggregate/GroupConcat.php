<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class GroupConcat extends AbstractAggregateOperator implements AggregateInterface
{
    protected OperatorInterface|TermInterface $expression;

    protected ?string $separator;

    public function __construct(OperatorInterface|TermInterface $expression, ?string $separator = null)
    {
        $this->expression = $expression;
        $this->separator = $separator;
    }

    public function serialize(): string
    {
        $exprString = $this->isDistinct
            ? sprintf('DISTINCT %s', $this->expression->serialize())
            : $this->expression->serialize();
        if ($this->separator !== null) {
            $escaped = str_replace(['\\', '"'], ['\\\\', '\\"'], $this->separator);
            return sprintf('GROUP_CONCAT(%s; SEPARATOR="%s")', $exprString, $escaped);
        }
        return sprintf('GROUP_CONCAT(%s)', $exprString);
    }
}
