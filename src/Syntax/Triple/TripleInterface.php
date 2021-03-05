<?php

namespace EffectiveActivism\SparQlClient\Syntax\Triple;

use EffectiveActivism\SparQlClient\Syntax\Term\TypeInterface;

interface TripleInterface
{
    /**
     * Getters.
     */

    public function getObject(): TypeInterface;

    public function getPredicate(): TypeInterface;

    public function getSubject(): TypeInterface;
}
