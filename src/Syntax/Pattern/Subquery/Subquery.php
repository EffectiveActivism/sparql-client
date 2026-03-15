<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery;

use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatementInterface;

class Subquery implements SubqueryInterface
{
    protected SelectStatementInterface $statement;

    public function __construct(SelectStatementInterface $statement)
    {
        $this->statement = $statement;
    }

    public function toArray(): array
    {
        return [];
    }

    public function getTerms(): array
    {
        return [];
    }

    public function serialize(): string
    {
        return sprintf('{ %s }', $this->statement->toQuery());
    }
}
