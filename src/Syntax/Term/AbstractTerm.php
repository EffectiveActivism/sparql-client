<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

abstract class AbstractTerm implements TermInterface
{
    protected string $variableName;

    public function getVariableName(): string
    {
        return $this->variableName;
    }

    public function setVariableName(string $variableName): TermInterface
    {
        $this->variableName = $variableName;
        return $this;
    }
}
