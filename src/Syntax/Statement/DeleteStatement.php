<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

class DeleteStatement extends AbstractConditionalStatement implements DeleteStatementInterface
{
    protected TripleInterface $tripleToDelete;

    public function __construct(TripleInterface $triple)
    {
        $this->tripleToDelete = $triple;
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
            return sprintf('%s DELETE { %s } WHERE { %s %s}', $query, (string) $this->tripleToDelete, $conditions, $optionalConditions);
        }
        else {
            return sprintf('%s DELETE { %s }', $query, (string) $this->tripleToDelete);
        }
    }
}
