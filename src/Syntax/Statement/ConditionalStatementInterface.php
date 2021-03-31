<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ConditionalStatementInterface extends StatementInterface
{
    public function where(array $triples): ConditionalStatementInterface;

    public function optionallyWhere(array $triples): ConditionalStatementInterface;

    /**
     * Getters.
     */

    public function getConditions(): array;

    public function getOptionalConditions(): array;
}
