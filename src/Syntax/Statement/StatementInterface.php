<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface StatementInterface
{
    public function toQuery(): string;

    public function getNamespaces(): array;

    public function withNamespaces(array $namespaces): static;
}
