<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface SelectStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $variables);
}
