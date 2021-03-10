<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface StatementInterface
{
    public function toQuery(): string;
}
