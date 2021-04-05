<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface SelectStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $variables, array $extraNamespaces = []);

    public function getVariables(): array;

    public function setVariables(array $variables): SelectStatementInterface;
}
