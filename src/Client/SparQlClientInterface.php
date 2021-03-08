<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\StatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\UpdateStatement;

interface SparQlClientInterface
{
    public function execute(StatementInterface $statement, bool $toTriples = false): array;

    public function select(array $variables): SelectStatement;

    public function update(array $variables): UpdateStatement;

    public function delete(array $variables): DeleteStatement;
}
