<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

class DeleteStatement extends AbstractConditionalStatement implements DeleteStatementInterface
{
    protected TripleInterface $tripleToDelete;

    public function __construct(TripleInterface $triple)
    {
        $this->tripleToDelete = $triple;
    }

    public function getQuery(): string
    {
        $namespaces = '';
        foreach ($this->extraNamespaces as $prefix => $url) {
            $namespaces .= sprintf('%s:%s ', $prefix, $url);
        }
        $conditions = sprintf('%s .', implode(' . ', $this->conditions));
        $optionalConditions = '';
        foreach ($this->optionalConditions as $triple) {
            $optionalConditions .= sprintf('OPTIONAL {%s} .', $triple);
        }
        return sprintf('%s DELETE %s WHERE {%s %s}', $namespaces, (string) $this->tripleToDelete, $conditions, $optionalConditions),
    }
}
