<?php

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Graph;

use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface GraphInterface extends PatternInterface
{
    public function getGraph(): AbstractIri;
}
