<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ConditionalStatementInterface extends StatementInterface
{
    public function where(array $patterns): ConditionalStatementInterface;

    /**
     * Getters.
     */

    public function getConditions(): array;
}
