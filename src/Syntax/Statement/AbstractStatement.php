<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;

abstract class AbstractStatement implements StatementInterface
{
    protected array $namespaces = [];

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
        ksort($this->namespaces);
        foreach ($this->namespaces as $prefix => $url) {
            $query .= sprintf('PREFIX %s: <%s> ', $prefix, $url);
        }
        return $query;
    }
}
