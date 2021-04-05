<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use InvalidArgumentException;

abstract class AbstractConditionalStatement extends AbstractStatement implements ConditionalStatementInterface
{
    /** @var PatternInterface[] */
    protected array $conditions = [];

    protected array $variables = [];

    public function where(array $patterns): ConditionalStatementInterface
    {
        foreach ($patterns as $pattern) {
            if (!($pattern instanceof PatternInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid condition class: %s', get_class($pattern)));
            }
            foreach ($pattern->toArray() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
        $this->conditions = $patterns;
        return $this;
    }

    /**
     * Getters.
     */

    public function getConditions(): array
    {
        return $this->conditions;
    }
}
