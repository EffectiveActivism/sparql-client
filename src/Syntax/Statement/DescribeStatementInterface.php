<?php

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

interface DescribeStatementInterface extends ConditionalStatementInterface, ResultTaggableInterface
{
    public function __construct(array $resources);

    public function limit(int $limit): DescribeStatementInterface;

    public function offset(int $offset): DescribeStatementInterface;

    public function orderBy(array $expressions): DescribeStatementInterface;

    public function getResources(): array;
}
