<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;

class ConstructResult implements ConstructResultInterface
{
    /**
     * @param Triple[] $triples
     */
    public function __construct(private readonly array $triples)
    {
    }

    public function getTriples(): array
    {
        return $this->triples;
    }
}
