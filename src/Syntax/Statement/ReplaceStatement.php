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

    public function toQuery(): string
    {
        $query = parent::toQuery();
        $conditions = '';
        foreach ($this->conditions as $triple) {
            $conditions .= sprintf('%s .', $triple);
        }
        $optionalConditions = '';
        foreach ($this->optionalConditions as $triple) {
            $optionalConditions .= sprintf('OPTIONAL {%s} .', $triple);
        }
        if (!empty($conditions) || !empty($optionalConditions)) {
            return sprintf('%s DELETE { %s } INSERT { %s } WHERE { %s %s}', $query, (string) $this->original, (string) $this->replacement, $conditions, $optionalConditions);
        }
        else {
            return sprintf('%s DELETE { %s } INSERT { %s }', $query, (string) $this->original, (string) $this->replacement);
        }
    }
}
