<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement, bool $toTriples = false): array|bool;

    public function construct(array $triples): ConstructStatementInterface;

    public function delete(TripleInterface $triple): DeleteStatementInterface;

    public function insert(TripleInterface $triple): InsertStatementInterface;

    public function replace(TripleInterface $triple): ReplaceStatementInterface;

    public function select(array $variables): SelectStatementInterface;

    /**
     * Getters.
     */

    public function getNamespaces(): array;

    /**
     * Setters.
     */

    public function setExtraNamespaces(array $extraNamespaces): SparQlClientInterface;
}
