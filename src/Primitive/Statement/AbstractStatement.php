<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Primitive\Statement;

use EffectiveActivism\SparQlClient\Primitive\Triple\TripleInterface;
use InvalidArgumentException;

abstract class AbstractStatement implements StatementInterface
{
    protected array $extraNamespaces = [];

    protected array $conditions = [];

    protected array $optionalConditions = [];

    protected array $variables = [];

    public function __construct(array $variables = [])
    {
        $this->variables = $variables;
    }

    public function condition(TripleInterface $triple): self
    {
        $this->conditions[] = $triple;
        return $this;
    }

    public function extraNamespaces(array $extraNamespaces): self
    {
        // Validate extra namespaces.
        foreach ($extraNamespaces as $prefix => $url) {
            // https://www.w3.org/TR/rdf-sparql-query/#rPN_LOCAL.
            if (preg_match('/([a-zA-Z0-9])([a-zA-Z]*)/', $prefix)) {
                throw new InvalidArgumentException('Invalid prefix format');
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException('Invalid URL format');
            }
        }
        $this->extraNamespaces = $extraNamespaces;
        return $this;
    }

    public function optionalCondition(TripleInterface $triple): self
    {
        $this->optionalConditions[] = $triple;
        return $this;
    }

    /**
     * Getters.
     */

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getExtraNamespaces(): array
    {
        return $this->extraNamespaces;
    }

    public function getOptionalConditions(): array
    {
        return $this->optionalConditions;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }
}
