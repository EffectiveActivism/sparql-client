<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

interface ReplaceStatementInterface extends ConditionalStatementInterface
{
    public function __construct(TripleInterface $triple);

    public function with(TripleInterface $triple): ReplaceStatementInterface;
}
