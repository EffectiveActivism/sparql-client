<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class SelectResult implements SelectResultInterface
{
    /**
     * @param array<int, array<string, TermInterface>> $rows
     */
    public function __construct(private readonly array $rows)
    {
    }

    public function getRows(): array
    {
        return $this->rows;
    }
}
