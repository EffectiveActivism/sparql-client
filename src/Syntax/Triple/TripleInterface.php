<?php

namespace EffectiveActivism\SparQlClient\Syntax\Triple;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface TripleInterface
{
    /**
     * Getters.
     */

    public function getObject(): TermInterface;

    public function getPredicate(): TermInterface;

    public function getSubject(): TermInterface;
}
