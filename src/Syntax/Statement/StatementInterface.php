<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

interface StatementInterface
{
    public function toQuery(): string;

    public function getNamespaces(): array;

    public function withNamespaces(array $namespaces): static;

    public function from(AbstractIri $iri): static;

    public function fromNamed(AbstractIri $iri): static;

    public function withBase(string $uri): static;
}
