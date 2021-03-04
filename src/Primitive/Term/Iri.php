<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Primitive\Term;

class Iri extends AbstractType implements TypeInterface
{
    protected function validate()
    {
        // TODO: Implement validate() method.
    }

    protected function serialize(): string
    {
        return sprintf('<%s>', $this->value);
    }
}
