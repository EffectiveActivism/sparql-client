<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    protected TripleInterface $original;

    protected TripleInterface $replacement;

    public function __construct(TripleInterface $triple, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->original = $triple;
    }

    public function with(TripleInterface $triple): ReplaceStatementInterface
    {
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
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

    public function getOriginal(): TripleInterface
    {
        return $this->original;
    }

    public function getReplacement(): TripleInterface
    {
        return $this->replacement;
    }
}
