<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Iri;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Iri extends AbstractIri implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-URI-reference.
     */
    protected string $value;

    /**
     * @throws SparQlException
     */
    public function __construct(string $value)
    {
        if ((!filter_var($value, FILTER_VALIDATE_URL) && !preg_match(sprintf('/%s/', Constant::URN), $value)) || preg_match(sprintf('/%s/u', Constant::CONTROL_CHARACTERS), $value)) {
            throw new SparQlException(sprintf('Value "%s" is not a valid RDF URI', $value));
        }
        $this->value = $value;
    }

    public function serialize(): string
    {
        return sprintf('<%s>', $this->value);
    }

    public function getRawValue(): string
    {
        return $this->value;
    }
}
