<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class InsertStatement extends AbstractStatement implements InsertStatementInterface
{
    protected TripleInterface $tripleToInsert;

    public function __construct(TripleInterface $triple, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->tripleToInsert = $triple;
    }

    public function toQuery(): string
    {
        $query = parent::toQuery();
        return sprintf('%s INSERT DATA { %s }', $query, (string) $this->tripleToInsert);
    }
}
