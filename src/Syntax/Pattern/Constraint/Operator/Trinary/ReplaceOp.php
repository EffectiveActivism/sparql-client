<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class ReplaceOp implements OperatorInterface
{
    protected OperatorInterface|TermInterface $arg;

    protected OperatorInterface|TermInterface $pattern;

    protected OperatorInterface|TermInterface $replacement;

    protected OperatorInterface|TermInterface|null $flags;

    public function __construct(
        OperatorInterface|TermInterface $arg,
        OperatorInterface|TermInterface $pattern,
        OperatorInterface|TermInterface $replacement,
        OperatorInterface|TermInterface|null $flags = null
    ) {
        $this->arg = $arg;
        $this->pattern = $pattern;
        $this->replacement = $replacement;
        $this->flags = $flags;
    }

    public function serialize(): string
    {
        if ($this->flags !== null) {
            return sprintf('REPLACE(%s,%s,%s,%s)', $this->arg->serialize(), $this->pattern->serialize(), $this->replacement->serialize(), $this->flags->serialize());
        }
        return sprintf('REPLACE(%s,%s,%s)', $this->arg->serialize(), $this->pattern->serialize(), $this->replacement->serialize());
    }
}
