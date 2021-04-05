<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Triple;

use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface TripleInterface extends PatternInterface
{
    /**
     * Getters.
     */

    public function getObject(): TermInterface;

    public function getPredicate(): TermInterface;

    public function getSubject(): TermInterface;

    /**
     * Setters.
     */

    public function setObject(TermInterface $term): TripleInterface;

    public function setPredicate(TermInterface $term): TripleInterface;

    public function setSubject(TermInterface $term): TripleInterface;
}
