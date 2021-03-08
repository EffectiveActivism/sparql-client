<?php

namespace EffectiveActivism\SparQlClient\Syntax\Term;

interface TermInterface
{
    public function getVariableName(): string;

    public function serialize(): string;

    public function setVariableName(string $variableName): TermInterface;
}
