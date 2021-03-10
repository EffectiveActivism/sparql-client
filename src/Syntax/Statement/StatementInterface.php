<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface StatementInterface
{
    public function extraNamespaces(array $extraNamespaces): StatementInterface;

    /**
     * Getters.
     */

    public function getConditions(): array;

    public function getExtraNamespaces(): array;

    public function getOptionalConditions(): array;

    public function getQuery(): string;

    public function getVariables(): array;
}
