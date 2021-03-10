<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

interface DeleteStatementInterface extends ConditionalStatementInterface
{
    public function __construct(TripleInterface $triple, array $extraNamespaces = []);

    /**
     * Getters.
     */

    public function getTripleToDelete(): TripleInterface;
}
