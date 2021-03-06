<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

abstract class AbstractIri implements TermInterface
{
    abstract public function serialize(): string;
}
