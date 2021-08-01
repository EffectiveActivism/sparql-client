<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

interface BindInterface extends AssignmentInterface
{
    public function getVariable(): Variable;
}
