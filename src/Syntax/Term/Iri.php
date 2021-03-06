<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

class Iri extends AbstractIri implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-URI-reference.
     */
    protected string $value;

    public function __construct(string $value)
    {
        if ((!filter_var($value, FILTER_VALIDATE_URL) && !preg_match(sprintf('/%s/', Constant::URN), $value)) || preg_match(sprintf('/%s/u', Constant::CONTROL_CHARACTERS), $value)) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid RDF URI', $value));
        }
        $this->value = $value;
    }

    public function serialize(): string
    {
        return sprintf('<%s>', $this->value);
    }
}
