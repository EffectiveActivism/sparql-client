<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ConditionalStatementInterface extends StatementInterface
{
    public function where(array $triples): ConditionalStatementInterface;
}
