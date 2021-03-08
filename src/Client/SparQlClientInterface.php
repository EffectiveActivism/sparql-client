<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatement;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement, bool $toTriples = false): array;

    public function delete(array $variables): DeleteStatement;

    public function insert(array $variables): InsertStatement;

    public function select(array $variables): SelectStatement;
}
