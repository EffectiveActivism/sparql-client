<?php

namespace EffectiveActivism\SparQlClient\Primitive\Triple;

use EffectiveActivism\SparQlClient\Primitive\Term\TypeInterface;

interface TripleInterface
{
    /**
     * Getters.
     */

    public function getObject(): TypeInterface;

    public function getPredicate(): TypeInterface;

    public function getSubject(): TypeInterface;
}
