<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface ReplaceStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples, array $extraNamespaces = []);

    public function with(array $triples): ReplaceStatementInterface;

    /**
     * Getters.
     */

    public function getOriginals(): array;

    public function getReplacements(): array;
}
