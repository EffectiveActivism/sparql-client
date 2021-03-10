<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

abstract class AbstractStatement implements StatementInterface
{
    protected array $extraNamespaces = [];

    public function extraNamespaces(array $extraNamespaces): StatementInterface
    {
        // Validate extra namespaces.
        foreach ($extraNamespaces as $prefix => $url) {
            if (preg_match(sprintf('/%s/', Constant::PN_LOCAL), $prefix)) {
                throw new InvalidArgumentException(sprintf('Value "%s" is not a valid prefix', $prefix));
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException(sprintf('Value "%s" is not a valid URL', $url));
            }
        }
        $this->extraNamespaces = $extraNamespaces;
        return $this;
    }

    public function toQuery(): string
    {
        $query = '';
        // TODO: Include config namespaces.
        foreach ($this->extraNamespaces as $prefix => $url) {
            $query .= sprintf('%s:%s ', $prefix, $url);
        }
        return $query;
    }

    /**
     * Getters.
     */

    public function getExtraNamespaces(): array
    {
        return $this->extraNamespaces;
    }
}
