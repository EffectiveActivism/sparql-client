<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use InvalidArgumentException;

class TypedLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-datatype-URI.
     */
    protected AbstractIri|null $dataType = null;

    public function __construct(bool|float|int|string $value, AbstractIri $dataType = null)
    {
        parent::__construct($value);
        $this->dataType = $dataType;
    }

    public function serialize(): string
    {
        if ($this->dataType === null) {
            return match (gettype($this->value)) {
                'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
                'double' => sprintf('"%s"^^xsd:decimal', $this->value),
                'integer' => sprintf('"%s"^^xsd:integer', $this->value),
                'string' => sprintf('%s%s%s', $this->serializeLiteralWrapper(), $this->value, $this->serializeLiteralWrapper()),
                default => throw new InvalidArgumentException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value))),
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

    /**
     * Getters.
     */

    public function getType(): string
    {
        if ($this->dataType === null) {
            return match (gettype($this->value)) {
                'boolean' => 'xsd:boolean',
                'double' => 'xsd:decimal',
                'integer' => 'xsd:integer',
                'string' => 'xsd:string',
                default => throw new InvalidArgumentException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value))),
            };
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'xsd:boolean',
            'http://www.w3.org/2001/XMLSchema#boolean'
        ])) {
            return 'xsd:boolean';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'xsd:decimal',
            'http://www.w3.org/2001/XMLSchema#decimal'
        ])) {
            return 'xsd:decimal';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#:nonPositiveInteger',
            'http://www.w3.org/2001/XMLSchema#:negativeInteger',
            'http://www.w3.org/2001/XMLSchema#:long',
            'http://www.w3.org/2001/XMLSchema#:int',
            'http://www.w3.org/2001/XMLSchema#:integer',
            'http://www.w3.org/2001/XMLSchema#:short',
            'http://www.w3.org/2001/XMLSchema#:byte',
            'http://www.w3.org/2001/XMLSchema#:nonNegativeInteger',
            'http://www.w3.org/2001/XMLSchema#:unsignedLong',
            'http://www.w3.org/2001/XMLSchema#:unsignedInt',
            'http://www.w3.org/2001/XMLSchema#:unsignedShort',
            'http://www.w3.org/2001/XMLSchema#:unsignedByte',
            'http://www.w3.org/2001/XMLSchema#:positiveInteger',
            'xsd:nonPositiveInteger',
            'xsd:negativeInteger',
            'xsd:long',
            'xsd:int',
            'xsd:integer',
            'xsd:short',
            'xsd:byte',
            'xsd:nonNegativeInteger',
            'xsd:unsignedLong',
            'xsd:unsignedInt',
            'xsd:unsignedShort',
            'xsd:unsignedByte',
            'xsd:positiveInteger',
        ])) {
            return 'xsd:integer';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#:string',
            'xsd:string',
        ])) {
            return 'xsd:string';
        }
        throw new InvalidArgumentException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value)));
    }
}
