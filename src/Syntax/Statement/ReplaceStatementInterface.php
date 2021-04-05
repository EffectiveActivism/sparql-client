<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

interface ReplaceStatementInterface extends ConditionalStatementInterface
{
    public function __construct(TripleInterface $triple, array $extraNamespaces = []);

    public function with(TripleInterface $triple): ReplaceStatementInterface;

    /**
     * Getters.
     */

    public function getOriginal(): TripleInterface;

    public function getReplacement(): TripleInterface;
}
