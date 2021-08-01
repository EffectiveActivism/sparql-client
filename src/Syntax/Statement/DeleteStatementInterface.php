<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface DeleteStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples, array $extraNamespaces = []);

    /**
     * Getters.
     */

    public function getTriplesToDelete(): array;
}
