<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

abstract class AbstractConditionalStatement extends AbstractStatement implements ConditionalStatementInterface
{
    protected array $conditions = [];

    protected array $optionalConditions = [];

    protected array $variables = [];

    public function where(array $triples, bool $optional = false): ConditionalStatementInterface
    {
        foreach ($triples as $triple) {
            if (!($triple instanceof TripleInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid condition class: %s', get_class($triple)));
            }
            foreach ($triple->toArray() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
        if ($optional) {
            $this->optionalConditions = $triples;
        }
        else {
            $this->conditions = $triples;
        }
        return $this;
    }

    /**
     * Getters.
     */

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getOptionalConditions(): array
    {
        return $this->optionalConditions;
    }
}
