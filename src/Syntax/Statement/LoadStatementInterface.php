<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface LoadStatementInterface extends StatementInterface
{
    public function silent(): static;

    public function into(AbstractIri $graph): static;

    /**
     * Getters.
     */

    public function getSource(): AbstractIri;

    public function getGraph(): ?AbstractIri;
}
