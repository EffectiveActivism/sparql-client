<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Bind implements BindInterface
{
    protected PatternInterface|OperatorInterface $value;

    protected Variable $variable;

    /**
     * @see https://www.w3.org/TR/sparql11-query/#bind.
     */
    public function __construct(PatternInterface|OperatorInterface $value, Variable $variable)
    {
        $this->value = $value;
        $this->variable = $variable;
    }

    public function toArray(): array
    {
        return $this->getTerms();
    }

    public function getTerms(): array
    {
        $terms = [$this->variable];
        if ($this->value instanceof PatternInterface) {
            foreach ($this->value->getTerms() as $item) {
                if ($item instanceof TermInterface) {
                    $terms[] = $item;
                }
            }
        }
        return $terms;
    }

    public function getVariable(): Variable
    {
        return $this->variable;
    }

    public function serialize(): string
    {
        return sprintf('BIND (%s AS %s )', $this->value->serialize(), $this->variable->serialize());
    }
}
