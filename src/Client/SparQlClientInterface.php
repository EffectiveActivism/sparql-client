<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Primitive\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Primitive\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Primitive\Statement\UpdateStatement;

interface SparQlClientInterface
{
    public function select(array $variables): SelectStatement;

    public function update(array $variables): UpdateStatement;

    public function delete(array $variables): DeleteStatement;
}
