<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery;

use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;

interface SubqueryInterface extends PatternInterface
{
    public function getInnerStatement(): SelectStatementInterface;
}
