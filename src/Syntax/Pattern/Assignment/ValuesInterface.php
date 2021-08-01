<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment;

interface ValuesInterface extends AssignmentInterface
{
    public function getVariables(): array;
}
