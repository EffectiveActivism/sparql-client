<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

class Iri implements TypeInterface
{
    protected string $value;

    public function __construct(string $value)
    {
        $this->value = $value;
    }

    public function validate(): bool
    {
        // TODO: Implement validate() method.
        return true;
    }

    public function serialize(): string
    {
        return sprintf('<%s>', $this->value);
    }
}
