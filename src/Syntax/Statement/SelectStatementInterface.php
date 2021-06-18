<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface SelectStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $variables, array $extraNamespaces = []);

    public function limit(int $limit): SelectStatementInterface;

    public function offset(int $offset): SelectStatementInterface;

    public function orderBy(array $expressions): SelectStatementInterface;

    public function getVariables(): array;

    public function setVariables(array $variables): SelectStatementInterface;
}
