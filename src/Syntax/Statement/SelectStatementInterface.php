<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;

interface SelectStatementInterface extends ConditionalStatementInterface
{
    public function __construct(array $variables);

    public function limit(int $limit): SelectStatementInterface;

    public function offset(int $offset): SelectStatementInterface;

    public function orderBy(array $expressions): SelectStatementInterface;

    public function distinct(): SelectStatementInterface;

    public function reduced(): SelectStatementInterface;

    public function groupBy(array $expressions): SelectStatementInterface;

    public function having(OperatorInterface $expression): SelectStatementInterface;

    public function getVariables(): array;

    public function setVariables(array $variables): SelectStatementInterface;
}
