<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

class PrefixedIri extends AbstractIri implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-URI-reference.
     */
    protected string|null $prefix;

    protected string|null $localPart;

    public function __construct(string $prefix = null, string $localPart = null)
    {
        // TODO: Check that prefix is defined.
        if (!preg_match(sprintf('/%s/u', Constant::PN_PREFIX), $prefix)) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid prefix', $prefix));
        }
        if (!preg_match(sprintf('/%s/u', Constant::PN_LOCAL), $localPart)) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid local part', $localPart));
        }
        $this->prefix = $prefix;
        $this->localPart = $localPart;
    }

    public function serialize(): string
    {
        return sprintf('%s:%s', $this->prefix, $this->localPart);
    }
}
