<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

interface StatementInterface
{
    public function condition(TripleInterface $triple): StatementInterface;

    public function extraNamespaces(array $extraNamespaces): StatementInterface;

    public function optionalCondition(TripleInterface $triple): StatementInterface;

    /**
     * Getters.
     */

    public function getConditions(): array;

    public function getExtraNamespaces(): array;

    public function getOptionalConditions(): array;

    public function getVariables(): array;
}
