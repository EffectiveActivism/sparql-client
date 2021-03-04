<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Primitive\Term;

class Literal extends AbstractType implements TypeInterface
{
    protected function validate()
    {
        // TODO: Implement validate() method.
    }

    protected function serialize(): string
    {
        return sprintf('"%s"', $this->value);
    }
}
