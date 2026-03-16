<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;

interface DescribeResultInterface extends StatementResultInterface
{
    /**
     * @return Triple[]
     */
    public function getTriples(): array;
}
