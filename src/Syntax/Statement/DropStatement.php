<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;

class DropStatement extends AbstractStatement implements DropStatementInterface
{
    protected bool $isSilent = false;

    public function __construct(protected AbstractIri $graph)
    {
    }

    public function silent(): static
    {
        $this->isSilent = true;
        return $this;
    }

    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $silent = $this->isSilent ? 'SILENT ' : '';
        return sprintf('%sDROP %sGRAPH %s', $preQuery, $silent, $this->graph->serialize());
    }

    public function getGraph(): AbstractIri
    {
        return $this->graph;
    }
}
