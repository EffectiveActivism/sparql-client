<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Primitive\Term;

abstract class AbstractType
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function __toString(): string
    {
        return $this->serialize();
    }

    abstract protected function serialize(): string;

    abstract protected function validate();
}
