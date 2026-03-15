<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class TypedLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-datatype-URI.
     */
    protected AbstractIri|null $dataType = null;

    /**
     * @throws SparQlException
     */
    public function __construct(bool|float|int|string $value, ?AbstractIri $dataType = null)
    {
        parent::__construct($value);
        $this->dataType = $dataType;
        $this->validateValue();
    }

    /**
     * @throws SparQlException
     */
    public function serialize(): string
    {
        if ($this->dataType === null) {
            return match (gettype($this->value)) {
                'boolean' => sprintf('"%s"^^<http://www.w3.org/2001/XMLSchema#boolean>', $this->value ? 'true' : 'false'),
                'double' => sprintf('"%s"^^<http://www.w3.org/2001/XMLSchema#decimal>', $this->value),
                'integer' => sprintf('"%s"^^<http://www.w3.org/2001/XMLSchema#integer>', $this->value),
                'string' => $this->sanitizeString(),
                default => throw new SparQlException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value))),
            };
        }
        elseif (in_array($this->dataType->serialize(), ['xsd:boolean', '<http://www.w3.org/2001/XMLSchema#boolean>'])) {
            if (is_bool($this->value)) {
                $value = $this->value ? 'true' : 'false';
            } elseif (is_string($this->value)) {
                $value = in_array(mb_strtolower($this->value), ['true', '1']) ? 'true' : 'false';
            } else {
                // int (0 or 1, already validated in constructor)
                $value = $this->value === 1 ? 'true' : 'false';
            }
            return sprintf('"%s"^^%s', $value, $this->dataType->serialize());
        }
        else {
            return sprintf('%s^^%s', $this->sanitizeString(), $this->dataType->serialize());
        }
    }

    /**
     * @throws SparQlException
     */
    private function validateValue(): void
    {
        if ($this->dataType === null) {
            return;
        }
        $rawType = $this->dataType->getRawValue();
        if (in_array($rawType, ['xsd:boolean', 'http://www.w3.org/2001/XMLSchema#boolean'])) {
            $this->validateBoolean();
        } elseif (in_array($rawType, ['xsd:date', 'http://www.w3.org/2001/XMLSchema#date'])) {
            if (!is_string($this->value) || !preg_match(sprintf('/%s/', Constant::XSD_DATE), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:date', $this->getRawValue()));
            }
        } elseif (in_array($rawType, ['xsd:dateTime', 'http://www.w3.org/2001/XMLSchema#dateTime'])) {
            if (!is_string($this->value) || !preg_match(sprintf('/%s/', Constant::XSD_DATETIME), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:dateTime', $this->getRawValue()));
            }
        } elseif (in_array($rawType, ['xsd:time', 'http://www.w3.org/2001/XMLSchema#time'])) {
            if (!is_string($this->value) || !preg_match(sprintf('/%s/', Constant::XSD_TIME), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:time', $this->getRawValue()));
            }
        } elseif (in_array($rawType, ['xsd:decimal', 'http://www.w3.org/2001/XMLSchema#decimal'])) {
            if (is_bool($this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:decimal', $this->getRawValue()));
            }
            if (is_string($this->value) && !preg_match(sprintf('/%s/', Constant::XSD_DECIMAL), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:decimal', $this->getRawValue()));
            }
        } elseif (in_array($rawType, [
            'http://www.w3.org/2001/XMLSchema#nonPositiveInteger',
            'http://www.w3.org/2001/XMLSchema#negativeInteger',
            'http://www.w3.org/2001/XMLSchema#long',
            'http://www.w3.org/2001/XMLSchema#int',
            'http://www.w3.org/2001/XMLSchema#integer',
            'http://www.w3.org/2001/XMLSchema#short',
            'http://www.w3.org/2001/XMLSchema#byte',
            'http://www.w3.org/2001/XMLSchema#nonNegativeInteger',
            'http://www.w3.org/2001/XMLSchema#unsignedLong',
            'http://www.w3.org/2001/XMLSchema#unsignedInt',
            'http://www.w3.org/2001/XMLSchema#unsignedShort',
            'http://www.w3.org/2001/XMLSchema#unsignedByte',
            'http://www.w3.org/2001/XMLSchema#positiveInteger',
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
            if (is_bool($this->value) || is_float($this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:integer', $this->getRawValue()));
            }
            if (is_string($this->value) && !preg_match(sprintf('/%s/', Constant::XSD_INTEGER), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:integer', $this->getRawValue()));
            }
        }
        // xsd:string accepts any value; unknown types are left for getType() to reject.
    }

    /**
     * @throws SparQlException
     */
    private function validateBoolean(): void
    {
        if (is_bool($this->value)) {
            return;
        }
        if (is_string($this->value) && in_array(mb_strtolower($this->value), ['true', 'false', '1', '0'])) {
            return;
        }
        if (is_int($this->value) && in_array($this->value, [0, 1])) {
            return;
        }
        throw new SparQlException(sprintf('Typed literal "%s" has invalid value for type "%s"', $this->getRawValue(), $this->dataType->serialize()));
    }

    /**
     * Getters.
     */

    /**
     * @throws SparQlException
     */
    public function getType(): string
    {
        if ($this->dataType === null) {
            return match (gettype($this->value)) {
                'boolean' => 'xsd:boolean',
                'double' => 'xsd:decimal',
                'integer' => 'xsd:integer',
                'string' => 'xsd:string',
                default => throw new SparQlException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value))),
            };
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#boolean',
            'xsd:boolean',
        ])) {
            return 'xsd:boolean';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#date',
            'xsd:date',
        ])) {
            return 'xsd:date';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#dateTime',
            'xsd:dateTime',
        ])) {
            return 'xsd:dateTime';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'xsd:decimal',
            'http://www.w3.org/2001/XMLSchema#decimal'
        ])) {
            return 'xsd:decimal';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#nonPositiveInteger',
            'http://www.w3.org/2001/XMLSchema#negativeInteger',
            'http://www.w3.org/2001/XMLSchema#long',
            'http://www.w3.org/2001/XMLSchema#int',
            'http://www.w3.org/2001/XMLSchema#integer',
            'http://www.w3.org/2001/XMLSchema#short',
            'http://www.w3.org/2001/XMLSchema#byte',
            'http://www.w3.org/2001/XMLSchema#nonNegativeInteger',
            'http://www.w3.org/2001/XMLSchema#unsignedLong',
            'http://www.w3.org/2001/XMLSchema#unsignedInt',
            'http://www.w3.org/2001/XMLSchema#unsignedShort',
            'http://www.w3.org/2001/XMLSchema#unsignedByte',
            'http://www.w3.org/2001/XMLSchema#positiveInteger',
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
            'http://www.w3.org/2001/XMLSchema#string',
            'xsd:string',
        ])) {
            return 'xsd:string';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'http://www.w3.org/2001/XMLSchema#time',
            'xsd:time',
        ])) {
            return 'xsd:time';
        }
        throw new SparQlException(sprintf('Typed literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value)));
    }
}
