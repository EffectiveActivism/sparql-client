<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractIri extends AbstractTerm implements TermInterface
{
    abstract public function serialize(): string;
}
