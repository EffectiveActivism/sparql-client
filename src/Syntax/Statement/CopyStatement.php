<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

class CopyStatement extends AbstractStatement implements CopyStatementInterface
{
    protected bool $isSilent = false;

    public function __construct(protected AbstractIri $sourceGraph, protected AbstractIri $destinationGraph)
    {
    }

    public function silent(): static
    {
        $this->isSilent = true;
        return $this;
    }

    public function toQuery(): string
    {
        if ($this->sourceGraph instanceof PrefixedIri && !array_key_exists($this->sourceGraph->getPrefix(), $this->namespaces)) {
            throw new SparQlException(sprintf('Prefix "%s" is not defined', $this->sourceGraph->getPrefix()));
        }
        if ($this->destinationGraph instanceof PrefixedIri && !array_key_exists($this->destinationGraph->getPrefix(), $this->namespaces)) {
            throw new SparQlException(sprintf('Prefix "%s" is not defined', $this->destinationGraph->getPrefix()));
        }
        $preQuery = parent::toQuery();
        $silent = $this->isSilent ? 'SILENT ' : '';
        return sprintf('%sCOPY %sGRAPH %s TO GRAPH %s', $preQuery, $silent, $this->sourceGraph->serialize(), $this->destinationGraph->serialize());
    }

    public function getSourceGraph(): AbstractIri
    {
        return $this->sourceGraph;
    }

    public function getDestinationGraph(): AbstractIri
    {
        return $this->destinationGraph;
    }
}
