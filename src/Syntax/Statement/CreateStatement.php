<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

class CreateStatement extends AbstractStatement implements CreateStatementInterface
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
        if ($this->graph instanceof PrefixedIri && !array_key_exists($this->graph->getPrefix(), $this->namespaces)) {
            throw new SparQlException(sprintf('Prefix "%s" is not defined', $this->graph->getPrefix()));
        }
        $preQuery = parent::toQuery();
        $silent = $this->isSilent ? 'SILENT ' : '';
        return sprintf('%sCREATE %sGRAPH %s', $preQuery, $silent, $this->graph->serialize());
    }

    public function getGraph(): AbstractIri
    {
        return $this->graph;
    }
}
