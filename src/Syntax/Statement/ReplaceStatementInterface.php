<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface ReplaceStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $triples);

    public function with(array $triples): ReplaceStatementInterface;

    public function usingGraph(AbstractIri $graph): static;

    /**
     * Getters.
     */

    public function getOriginals(): array;

    public function getReplacements(): array;

    public function getScopeGraph(): ?AbstractIri;
}
