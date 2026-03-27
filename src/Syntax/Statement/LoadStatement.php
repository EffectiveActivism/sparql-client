<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

class LoadStatement extends AbstractStatement implements LoadStatementInterface
{
    protected bool $isSilent = false;

    protected ?AbstractIri $graph = null;

    public function __construct(protected AbstractIri $source)
    {
    }

    public function silent(): static
    {
        $this->isSilent = true;
        return $this;
    }

    public function into(AbstractIri $graph): static
    {
        $this->graph = $graph;
        return $this;
    }

    public function toQuery(): string
    {
        if ($this->source instanceof PrefixedIri && !array_key_exists($this->source->getPrefix(), $this->namespaces)) {
            throw new SparQlException(sprintf('Prefix "%s" is not defined', $this->source->getPrefix()));
        }
        if ($this->graph instanceof PrefixedIri && !array_key_exists($this->graph->getPrefix(), $this->namespaces)) {
            throw new SparQlException(sprintf('Prefix "%s" is not defined', $this->graph->getPrefix()));
        }
        $preQuery = parent::toQuery();
        $silent = $this->isSilent ? 'SILENT ' : '';
        $into = $this->graph !== null ? sprintf(' INTO GRAPH %s', $this->graph->serialize()) : '';
        return sprintf('%sLOAD %s%s%s', $preQuery, $silent, $this->source->serialize(), $into);
    }

    public function getSource(): AbstractIri
    {
        return $this->source;
    }

    public function getGraph(): ?AbstractIri
    {
        return $this->graph;
    }
}
