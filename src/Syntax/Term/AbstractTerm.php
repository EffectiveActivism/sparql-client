<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

abstract class AbstractTerm implements TermInterface
{
    protected string|null $variableName = null;

    public function getVariableName(): string|null
    {
        return $this->variableName;
    }

    public function setVariableName(string $variableName): TermInterface
    {
        $this->variableName = $variableName;
        return $this;
    }
}
