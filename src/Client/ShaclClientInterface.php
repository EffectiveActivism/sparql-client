<?php

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\InsertStatementInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatementInterface;
use EffectiveActivism\SparQlClient\Validation\ValidationResultInterface;

interface ShaclClientInterface
{
    public function convertToConstructStatement(ConstructStatementInterface|DeleteStatementInterface|InsertStatementInterface|ReplaceStatementInterface $statement): ConstructStatementInterface;

    public function validate(ConstructStatementInterface $statement): ValidationResultInterface;

    /**
     * Getters.
     */

    public function getNamespaces(): array;

    /**
     * Setters.
     */

    public function setExtraNamespaces(array $extraNamespaces): ShaclClientInterface;
}
