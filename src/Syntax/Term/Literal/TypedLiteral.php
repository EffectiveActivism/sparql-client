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
     * Bounded integer subtypes and their [min, max] range.
     * Null means unbounded in that direction.
     * xsd:integer is absent because it is unbounded in both directions.
     */
    private const INTEGER_RANGES = [
        'xsd:long'                                             => ['-9223372036854775808', '9223372036854775807'],
        'http://www.w3.org/2001/XMLSchema#long'               => ['-9223372036854775808', '9223372036854775807'],
        'xsd:int'                                              => ['-2147483648', '2147483647'],
        'http://www.w3.org/2001/XMLSchema#int'                => ['-2147483648', '2147483647'],
        'xsd:short'                                            => ['-32768', '32767'],
        'http://www.w3.org/2001/XMLSchema#short'              => ['-32768', '32767'],
        'xsd:byte'                                             => ['-128', '127'],
        'http://www.w3.org/2001/XMLSchema#byte'               => ['-128', '127'],
        'xsd:nonNegativeInteger'                               => ['0', null],
        'http://www.w3.org/2001/XMLSchema#nonNegativeInteger'  => ['0', null],
        'xsd:unsignedLong'                                     => ['0', '18446744073709551615'],
        'http://www.w3.org/2001/XMLSchema#unsignedLong'       => ['0', '18446744073709551615'],
        'xsd:unsignedInt'                                      => ['0', '4294967295'],
        'http://www.w3.org/2001/XMLSchema#unsignedInt'        => ['0', '4294967295'],
        'xsd:unsignedShort'                                    => ['0', '65535'],
        'http://www.w3.org/2001/XMLSchema#unsignedShort'      => ['0', '65535'],
        'xsd:unsignedByte'                                     => ['0', '255'],
        'http://www.w3.org/2001/XMLSchema#unsignedByte'       => ['0', '255'],
        'xsd:nonPositiveInteger'                               => [null, '0'],
        'http://www.w3.org/2001/XMLSchema#nonPositiveInteger'  => [null, '0'],
        'xsd:negativeInteger'                                  => [null, '-1'],
        'http://www.w3.org/2001/XMLSchema#negativeInteger'    => [null, '-1'],
        'xsd:positiveInteger'                                  => ['1', null],
        'http://www.w3.org/2001/XMLSchema#positiveInteger'    => ['1', null],
    ];

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
            $this->validateDateComponents($rawType);
            $this->validateTimezoneOffset($rawType);
        } elseif (in_array($rawType, ['xsd:dateTime', 'http://www.w3.org/2001/XMLSchema#dateTime'])) {
            if (!is_string($this->value) || !preg_match(sprintf('/%s/', Constant::XSD_DATETIME), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:dateTime', $this->getRawValue()));
            }
            $this->validateDateComponents($rawType);
            $this->validateTimeComponents($rawType);
            $this->validateTimezoneOffset($rawType);
        } elseif (in_array($rawType, ['xsd:time', 'http://www.w3.org/2001/XMLSchema#time'])) {
            if (!is_string($this->value) || !preg_match(sprintf('/%s/', Constant::XSD_TIME), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:time', $this->getRawValue()));
            }
            $this->validateTimeComponents($rawType);
            $this->validateTimezoneOffset($rawType);
        } elseif (in_array($rawType, ['xsd:decimal', 'http://www.w3.org/2001/XMLSchema#decimal'])) {
            if (is_bool($this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:decimal', $this->getRawValue()));
            }
            if (is_float($this->value) && (is_infinite($this->value) || is_nan($this->value))) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:decimal', var_export($this->value, true)));
            }
            if (is_string($this->value) && !preg_match(sprintf('/%s/', Constant::XSD_DECIMAL), $this->value)) {
                throw new SparQlException(sprintf('Value "%s" is not valid for type xsd:decimal', $this->getRawValue()));
            }
        } elseif (in_array($rawType, [
            'xsd:float',
            'http://www.w3.org/2001/XMLSchema#float',
            'xsd:double',
            'http://www.w3.org/2001/XMLSchema#double',
        ])) {
            $this->validateFloatDouble($rawType);
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
            $this->validateIntegerSubtype($rawType);
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
     * Validates month/day calendar consistency after the regex format check passes.
     * Applies to xsd:date and xsd:dateTime.
     *
     * @throws SparQlException
     */
    private function validateDateComponents(string $rawType): void
    {
        preg_match('/^(-?)(\d{4,})-(\d{2})-(\d{2})/', $this->value, $matches);
        // Convert XSD year to astronomical year: XSD 0000 = 1 BCE = astronomical 0,
        // XSD -0001 = 2 BCE = astronomical -1.
        $astronomicalYear = $matches[1] === '-' ? -(int) $matches[2] : (int) $matches[2];
        $month = (int) $matches[3];
        $day = (int) $matches[4];
        if ($month < 1 || $month > 12 || $day < 1) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
        $daysInMonth = [0, 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
        $isLeap = ($astronomicalYear % 4 === 0 && ($astronomicalYear % 100 !== 0 || $astronomicalYear % 400 === 0));
        if ($isLeap) {
            $daysInMonth[2] = 29;
        }
        if ($day > $daysInMonth[$month]) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
    }

    /**
     * Validates hour/minute/second ranges after the regex format check passes.
     * Applies to xsd:time and xsd:dateTime.
     * XSD allows 24:00:00 to represent end-of-day midnight.
     *
     * @throws SparQlException
     */
    private function validateTimeComponents(string $rawType): void
    {
        $timeStr = str_contains($this->value, 'T')
            ? substr($this->value, strpos($this->value, 'T') + 1)
            : $this->value;
        preg_match('/^(\d{2}):(\d{2})(?::(\d{2}))?/', $timeStr, $matches);
        $hour = (int) $matches[1];
        $minute = (int) $matches[2];
        $second = isset($matches[3]) ? (int) $matches[3] : 0;
        // 24:00:00 is the only valid end-of-day form; fractional seconds invalidate it.
        $endOfDay = ($hour === 24 && $minute === 0 && $second === 0
            && !preg_match('/^24:00:00\./', $timeStr));
        if (!$endOfDay && ($hour > 23 || $minute > 59 || $second > 59)) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
    }

    /**
     * Validates the timezone offset suffix, if present.
     * XSD restricts offsets to -14:00 through +14:00 with minutes in 0–59.
     * 'Z' and the absence of a timezone suffix are always valid.
     *
     * @throws SparQlException
     */
    private function validateTimezoneOffset(string $rawType): void
    {
        if (!preg_match('/([+-])(\d{2}):(\d{2})$/', $this->value, $matches)) {
            return;
        }
        $hour = (int) $matches[2];
        $minute = (int) $matches[3];
        if ($hour > 14 || $minute > 59 || ($hour === 14 && $minute !== 0)) {
            throw new SparQlException(sprintf('Value "%s" has an invalid timezone offset for type %s', $this->getRawValue(), $rawType));
        }
    }

    /**
     * @throws SparQlException
     */
    private function validateFloatDouble(string $rawType): void
    {
        if (is_bool($this->value)) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
        if (is_string($this->value) && !preg_match(sprintf('/%s/', Constant::XSD_FLOAT), $this->value)) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
        // PHP float and int values are always valid for xsd:float/xsd:double.
    }

    /**
     * Validates format, type compatibility, and range for integer subtypes.
     *
     * @throws SparQlException
     */
    private function validateIntegerSubtype(string $rawType): void
    {
        if (is_bool($this->value) || is_float($this->value)) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
        if (is_string($this->value) && !preg_match(sprintf('/%s/', Constant::XSD_INTEGER), $this->value)) {
            throw new SparQlException(sprintf('Value "%s" is not valid for type %s', $this->getRawValue(), $rawType));
        }
        if (!isset(self::INTEGER_RANGES[$rawType])) {
            return; // xsd:integer — no range constraint
        }
        $stringValue = ltrim((string) $this->value, '+');
        [$min, $max] = self::INTEGER_RANGES[$rawType];
        if ($min !== null && self::compareIntegers($stringValue, $min) < 0) {
            throw new SparQlException(sprintf('Value "%s" is out of range for type %s', $this->getRawValue(), $rawType));
        }
        if ($max !== null && self::compareIntegers($stringValue, $max) > 0) {
            throw new SparQlException(sprintf('Value "%s" is out of range for type %s', $this->getRawValue(), $rawType));
        }
    }

    /**
     * Compares two arbitrary-precision integer strings.
     * Returns negative, zero, or positive like strcmp.
     */
    private static function compareIntegers(string $a, string $b): int
    {
        $aPositive = !str_starts_with($a, '-');
        $bPositive = !str_starts_with($b, '-');
        // Normalize: strip leading zeros from the absolute value; collapse to "0" if empty.
        $aAbs = ltrim(ltrim($a, '-'), '0') ?: '0';
        $bAbs = ltrim(ltrim($b, '-'), '0') ?: '0';
        // Canonicalize -0 and +0 to positive zero.
        if ($aAbs === '0') {
            $aPositive = true;
        }
        if ($bAbs === '0') {
            $bPositive = true;
        }
        if ($aPositive !== $bPositive) {
            return $aPositive ? 1 : -1;
        }
        $lenA = strlen($aAbs);
        $lenB = strlen($bAbs);
        if ($lenA !== $lenB) {
            $result = $lenA <=> $lenB;
        } else {
            $result = strcmp($aAbs, $bAbs);
        }
        return $aPositive ? $result : -$result;
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
            'xsd:float',
            'http://www.w3.org/2001/XMLSchema#float',
        ])) {
            return 'xsd:float';
        }
        elseif (in_array($this->dataType->getRawValue(), [
            'xsd:double',
            'http://www.w3.org/2001/XMLSchema#double',
        ])) {
            return 'xsd:double';
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
