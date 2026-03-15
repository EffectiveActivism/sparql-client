<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;

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
