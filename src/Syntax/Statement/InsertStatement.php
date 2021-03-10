<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;

class InsertStatement extends AbstractStatement implements InsertStatementInterface
{
    protected TripleInterface $tripleToInsert;

    public function __construct(TripleInterface $triple)
    {
        $this->tripleToInsert = $triple;
    }
}
