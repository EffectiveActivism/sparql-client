<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;

abstract class AbstractStatement implements StatementInterface
{
    protected array $namespaces = [];

    protected array $fromDatasets = [];

    protected array $fromNamedDatasets = [];

    protected ?string $baseUri = null;

    /**
     * @throws SparQlException
     */
    public function withNamespaces(array $namespaces): static
    {
        foreach ($namespaces as $prefix => $url) {
            if (!is_string($prefix) || !preg_match(sprintf('/%s/u', Constant::PN_PREFIX), $prefix)) {
                throw new SparQlException(sprintf('Value "%s" is not a valid prefix', $prefix));
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new SparQlException(sprintf('Value "%s" is not a valid URL', $url));
            }
        }
        $this->namespaces = array_merge($this->namespaces, $namespaces);
        return $this;
    }

    public function from(AbstractIri $iri): static
    {
        $this->fromDatasets[] = $iri;
        return $this;
    }

    public function fromNamed(AbstractIri $iri): static
    {
        $this->fromNamedDatasets[] = $iri;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function withBase(string $uri): static
    {
        if ((!filter_var($uri, FILTER_VALIDATE_URL) && !preg_match(sprintf('/%s/', Constant::URN), $uri)) || preg_match(sprintf('/%s/u', Constant::CONTROL_CHARACTERS), $uri)) {
            throw new SparQlException(sprintf('Value "%s" is not a valid base URI', $uri));
        }
        $this->baseUri = $uri;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    protected function validatePrefixes(array $items): void
    {
        foreach ($items as $item) {
            foreach ($item->getTerms() as $term) {
                if (get_class($term) === \EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new SparQlException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function toQuery(): string
    {
        $query = '';
        if ($this->baseUri !== null) {
            $query .= sprintf('BASE <%s> ', $this->baseUri);
        }
        ksort($this->namespaces);
        foreach ($this->namespaces as $prefix => $url) {
            $query .= sprintf('PREFIX %s: <%s> ', $prefix, $url);
        }
        return $query;
    }

    /**
     * @throws SparQlException
     */
    protected function getDatasetClausesString(): string
    {
        $string = '';
        foreach ($this->fromDatasets as $iri) {
            if ($iri instanceof PrefixedIri && !in_array($iri->getPrefix(), array_keys($this->namespaces))) {
                throw new SparQlException(sprintf('Prefix "%s" is not defined', $iri->getPrefix()));
            }
            $string .= sprintf('FROM %s ', $iri->serialize());
        }
        foreach ($this->fromNamedDatasets as $iri) {
            if ($iri instanceof PrefixedIri && !in_array($iri->getPrefix(), array_keys($this->namespaces))) {
                throw new SparQlException(sprintf('Prefix "%s" is not defined', $iri->getPrefix()));
            }
            $string .= sprintf('FROM NAMED %s ', $iri->serialize());
        }
        return $string;
    }
}
