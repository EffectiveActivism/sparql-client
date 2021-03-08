<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

abstract class AbstractIri extends AbstractTerm implements TermInterface
{
    abstract public function serialize(): string;
}
