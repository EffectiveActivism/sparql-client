<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface StatementInterface
{
    public function extraNamespaces(array $extraNamespaces): StatementInterface;

    public function toQuery(): string;

    /**
     * Getters.
     */

    public function getExtraNamespaces(): array;
}
