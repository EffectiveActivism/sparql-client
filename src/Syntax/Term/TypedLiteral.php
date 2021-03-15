<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use InvalidArgumentException;

class TypedLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-datatype-URI.
     */
    protected AbstractIri|null $dataType;

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
        if (empty($this->dataType)) {
            return match (gettype($this->value)) {
                'string' => sprintf('%s%s%s', $this->serializeLiteralWrapper(), $this->value, $this->serializeLiteralWrapper()),
                'integer' => sprintf('"%s"^^xsd:integer', $this->value),
                'double' => sprintf('"%s"^^xsd:decimal', $this->value),
                'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
                default => null,
            };
        }
        elseif (in_array($this->dataType->serialize(), ['xsd:boolean', 'http://www.w3.org/2001/XMLSchema#boolean'])) {
            $value = 'true';
            if (is_string($this->value) && ($this->value === 'false' || $this->value === '0')) {
                $value = 'false';
            }
            if (is_bool($this->value)) {
                $value = $this->value ? 'true' : 'false';
            }
            if (is_integer($this->value)) {
                $value = $this->value > 0 ? 'true' : 'false';
            }
            return sprintf('"%s"^^%s', $value, $this->dataType->serialize());
        }
        else {
            return sprintf('"%s"^^%s', $this->value, $this->dataType->serialize());
        }
    }
}
