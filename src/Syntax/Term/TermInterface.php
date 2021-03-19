<?php

namespace EffectiveActivism\SparQlClient\Syntax\Term;

interface TermInterface
{
    /**
     * Getters.
     */

    public function getRawValue(): string;

    public function getVariableName(): string;


    /**
     * Setters.
     */

    public function serialize(): string;

    public function setVariableName(string $variableName): TermInterface;
}
