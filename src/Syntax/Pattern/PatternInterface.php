<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern;

interface PatternInterface
{
    public function toArray(): array;

    public function getTerms(): array;

    public function serialize(): string;
}
