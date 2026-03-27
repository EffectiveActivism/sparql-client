<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface AddStatementInterface extends StatementInterface
{
    public function silent(): static;

    /**
     * Getters.
     */

    public function getSourceGraph(): AbstractIri;

    public function getDestinationGraph(): AbstractIri;
}
