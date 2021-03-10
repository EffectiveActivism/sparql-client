<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

abstract class AbstractStatement implements StatementInterface
{
    protected array $extraNamespaces = [];

    protected array $conditions = [];

    protected array $optionalConditions = [];

    protected array $variables = [];

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

    /**
     * Getters.
     */

    public function getConditions(): array
    {
        return $this->conditions;
    }

    public function getExtraNamespaces(): array
    {
        return $this->extraNamespaces;
    }

    public function getOptionalConditions(): array
    {
        return $this->optionalConditions;
    }

    abstract public function getQuery(): string;

    public function getVariables(): array
    {
        return $this->variables;
    }
}
