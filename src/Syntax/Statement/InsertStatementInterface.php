<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

interface InsertStatementInterface extends ConditionalStatementInterface
{
    public function __construct(TripleInterface $triple, array $extraNamespaces = []);
}
