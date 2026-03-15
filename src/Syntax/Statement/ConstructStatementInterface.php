<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ConstructStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples);

    /**
     * Getters.
     */

    public function getTriplesToConstruct(): array;
}
