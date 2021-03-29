<?php

namespace EffectiveActivism\SparQlClient\Syntax\Term;

interface TermInterface
{
    public function serialize(): string;

    /**
     * Getters.
     */

    public function getRawValue(): string;

    public function getVariableName(): string;

    /**
     * Setters.
     */

    public function setVariableName(string $variableName): TermInterface;
}
