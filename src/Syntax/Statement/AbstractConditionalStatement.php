<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

abstract class AbstractConditionalStatement extends AbstractStatement implements ConditionalStatementInterface
{
    public function where(array $triples, bool $optional = false): ConditionalStatementInterface
    {
        foreach ($triples as $triple) {
            if (!($triple instanceof TripleInterface)) {
                throw new InvalidArgumentException(sprintf('Invalid condition class: %s', get_class($triple)));
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
}
