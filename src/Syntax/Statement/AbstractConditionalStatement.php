<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Constraint\ConstraintInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

abstract class AbstractConditionalStatement extends AbstractStatement implements ConditionalStatementInterface
{
    protected array $conditions = [];

    protected array $optionalConditions = [];

    protected array $variables = [];

    public function where(array $triplesOrConstraints): ConditionalStatementInterface
    {
        foreach ($triplesOrConstraints as $tripleorConstraint) {
            if (!($tripleorConstraint instanceof TripleInterface) && !($tripleorConstraint instanceof ConstraintInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid condition class: %s', get_class($tripleorConstraint)));
            }
            foreach ($tripleorConstraint->toArray() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
        $this->conditions = $triplesOrConstraints;
        return $this;
    }

    public function optionallyWhere(array $triplesOrConstraints): ConditionalStatementInterface
    {
        foreach ($triplesOrConstraints as $tripleorConstraint) {
            if (!($tripleorConstraint instanceof TripleInterface) && !($tripleorConstraint instanceof ConstraintInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid condition class: %s', get_class($tripleorConstraint)));
            }
            foreach ($tripleorConstraint->toArray() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
        $this->optionalConditions = $triplesOrConstraints;
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
