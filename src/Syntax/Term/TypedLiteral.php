<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use InvalidArgumentException;

class TypedLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-datatype-URI.
     */
    protected PrefixedIri|null $dataType;

    public function __construct(bool|float|int|string $value, AbstractIri $dataType = null)
    {
        parent::__construct($value);
        if ($dataType !== null && false) {
            throw new InvalidArgumentException(sprintf('Datatype "%s" is not valid', $dataType->serialize()));
        }
        $this->dataType = $dataType;
    }

    public function serialize(): string
    {
        return match (gettype($this->value)) {
            'string' => sprintf('"""%s"""', $this->value),
            'integer' => sprintf('"%s"^^xsd:integer', $this->value),
            'double' => sprintf('"%s"^^xsd:decimal', $this->value),
            'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
            default => null,
        };
    }
}
