<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface InsertStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples);

    /**
     * Getters.
     */

    public function getTriplesToInsert(): array;
}
