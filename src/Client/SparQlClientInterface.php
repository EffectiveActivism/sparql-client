<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement, bool $toTriples = false): array;

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
