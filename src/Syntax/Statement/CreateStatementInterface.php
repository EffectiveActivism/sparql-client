<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface CreateStatementInterface extends StatementInterface
{
    public function silent(): static;

    /**
     * Getters.
     */

    public function getGraph(): AbstractIri;
}
