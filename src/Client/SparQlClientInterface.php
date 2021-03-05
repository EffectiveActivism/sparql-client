<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Statement\UpdateStatement;

interface SparQlClientInterface
{
    public function select(array $variables): SelectStatement;

    public function update(array $variables): UpdateStatement;

    public function delete(array $variables): DeleteStatement;
}
