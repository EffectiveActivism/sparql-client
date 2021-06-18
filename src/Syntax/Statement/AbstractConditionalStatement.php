<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

abstract class AbstractConditionalStatement extends AbstractStatement implements ConditionalStatementInterface
{
    /** @var PatternInterface[] */
    protected array $conditions = [];

    /**
     * @throws SparQlException
     */
    public function where(array $patterns): ConditionalStatementInterface
    {
        foreach ($patterns as $pattern) {
            if (!($pattern instanceof PatternInterface)) {
                throw new SparQlException(sprintf('Invalid condition class: %s', get_class($pattern)));
            }
            foreach ($pattern->getTerms() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new SparQlException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
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
