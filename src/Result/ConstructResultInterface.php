<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;

interface ConstructResultInterface extends StatementResultInterface
{
    /**
     * @return Triple[]
     */
    public function getTriples(): array;
}
