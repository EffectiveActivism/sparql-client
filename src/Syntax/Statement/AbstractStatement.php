<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

abstract class AbstractStatement implements StatementInterface
{
    protected array $namespaces = [];

    public function __construct(array $namespaces)
    {
        // Validate extra namespaces.
        foreach ($namespaces as $prefix => $url) {
            if (!is_string($prefix) || !preg_match(sprintf('/%s/u', Constant::PN_LOCAL), $prefix)) {
                throw new InvalidArgumentException(sprintf('Value "%s" is not a valid prefix', $prefix));
            }
            if (!filter_var($url, FILTER_VALIDATE_URL)) {
                throw new InvalidArgumentException(sprintf('Value "%s" is not a valid URL', $url));
            }
        }
        $this->namespaces = $namespaces;
    }

    public function toQuery(): string
    {
        $query = '';
        // TODO: Include config namespaces.
        foreach ($this->namespaces as $prefix => $url) {
            $query .= sprintf('PREFIX %s:%s; ', $prefix, $url);
        }
        return $query;
    }
}
