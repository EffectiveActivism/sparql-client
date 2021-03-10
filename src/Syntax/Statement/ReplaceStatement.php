<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    protected TripleInterface $original;

    protected TripleInterface $replacement;

    public function __construct(TripleInterface $triple)
    {
        $this->original = $triple;
    }

    public function with(TripleInterface $triple): ReplaceStatementInterface
    {
        $this->replacement = $triple;
        return $this;
    }
}
