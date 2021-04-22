<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ConstructStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples, array $extraNamespaces = []);

    /**
     * Getters.
     */

    public function getTripleToConstruct(): array;
}
