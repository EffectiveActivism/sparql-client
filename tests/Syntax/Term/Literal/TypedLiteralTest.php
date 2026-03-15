<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class TypedLiteralTest extends KernelTestCase
{
    public function testStringTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('lorem');
        $this->assertEquals('"""lorem"""', $typedLiteral->serialize());
        $this->assertEquals('xsd:string', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('lorem', new PrefixedIri('xsd', 'string'));
        $this->assertEquals('"""lorem"""^^xsd:string', $typedLiteral->serialize());
        $this->assertEquals('xsd:string', $typedLiteral->getType());
    }

    public function testIntegerTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2);
        $this->assertEquals('"2"^^<http://www.w3.org/2001/XMLSchema#integer>', $typedLiteral->serialize());
        $this->assertEquals('xsd:integer', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(2, new PrefixedIri('xsd', 'integer'));
        $this->assertEquals('"""2"""^^xsd:integer', $typedLiteral->serialize());
        $this->assertEquals('xsd:integer', $typedLiteral->getType());
    }

    public function testDoubleTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2.4);
        $this->assertEquals('"2.4"^^<http://www.w3.org/2001/XMLSchema#decimal>', $typedLiteral->serialize());
        $this->assertEquals('xsd:decimal', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(2.4, new PrefixedIri('xsd', 'decimal'));
        $this->assertEquals('"""2.4"""^^xsd:decimal', $typedLiteral->serialize());
        $this->assertEquals('xsd:decimal', $typedLiteral->getType());
    }

    public function testBooleanTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(true);
        $this->assertEquals('"true"^^<http://www.w3.org/2001/XMLSchema#boolean>', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('True', new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(true, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('false', new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(1, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"true"^^xsd:boolean', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(0, new PrefixedIri('xsd', 'boolean'));
        $this->assertEquals('"false"^^xsd:boolean', $typedLiteral->serialize());
        $this->assertEquals('xsd:boolean', $typedLiteral->getType());
    }

    public function testBooleanTypedLiteralInvalidValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(22, new PrefixedIri('xsd', 'boolean'));
    }

    public function testDateTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('not-a-date', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTypedLiteralNonString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(42, new PrefixedIri('xsd', 'date'));
    }

    public function testDateTimeTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('not-a-datetime', new PrefixedIri('xsd', 'dateTime'));
    }

    public function testDateTimeTypedLiteralNonString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(42, new PrefixedIri('xsd', 'dateTime'));
    }

    public function testTimeTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('not-a-time', new PrefixedIri('xsd', 'time'));
    }

    public function testTimeTypedLiteralNonString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(42, new PrefixedIri('xsd', 'time'));
    }

    public function testDecimalTypedLiteralBoolValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(true, new PrefixedIri('xsd', 'decimal'));
    }

    public function testDecimalTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('abc', new PrefixedIri('xsd', 'decimal'));
    }

    public function testIntegerTypedLiteralFloatValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(3.14, new PrefixedIri('xsd', 'integer'));
    }

    public function testIntegerTypedLiteralBoolValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(true, new PrefixedIri('xsd', 'integer'));
    }

    public function testIntegerTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('3.14', new PrefixedIri('xsd', 'integer'));
    }

    public function testDateTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('2020-01-01+01:00', new PrefixedIri('xsd', 'date'));
        $this->assertEquals('"""2020-01-01+01:00"""^^xsd:date', $typedLiteral->serialize());
        $this->assertEquals('xsd:date', $typedLiteral->getType());
    }

    public function testDateTimeTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('2020-01-01T12:01+01:00', new PrefixedIri('xsd', 'dateTime'));
        $this->assertEquals('"""2020-01-01T12:01+01:00"""^^xsd:dateTime', $typedLiteral->serialize());
        $this->assertEquals('xsd:dateTime', $typedLiteral->getType());
    }

    public function testTimeTypedLiteral()
    {
        $typedLiteral = new TypedLiteral('12:01+01:00', new PrefixedIri('xsd', 'time'));
        $this->assertEquals('"""12:01+01:00"""^^xsd:time', $typedLiteral->serialize());
        $this->assertEquals('xsd:time', $typedLiteral->getType());
    }

    public function testUnknownType()
    {
        $typedLiteral = new TypedLiteral('lorem', new PrefixedIri('xsd', 'unknown'));
        $this->expectException(SparQlException::class);
        $typedLiteral->getType();
    }

    // xsd:float and xsd:double

    public function testFloatTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2.4, new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""2.4"""^^xsd:float', $typedLiteral->serialize());
        $this->assertEquals('xsd:float', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral(2, new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""2"""^^xsd:float', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral('1.5E10', new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""1.5E10"""^^xsd:float', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral('INF', new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""INF"""^^xsd:float', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral('-INF', new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""-INF"""^^xsd:float', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral('NaN', new PrefixedIri('xsd', 'float'));
        $this->assertEquals('"""NaN"""^^xsd:float', $typedLiteral->serialize());
    }

    public function testXsdDoubleTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(2.4, new PrefixedIri('xsd', 'double'));
        $this->assertEquals('"""2.4"""^^xsd:double', $typedLiteral->serialize());
        $this->assertEquals('xsd:double', $typedLiteral->getType());
        $typedLiteral = new TypedLiteral('1.5E100', new PrefixedIri('xsd', 'double'));
        $this->assertEquals('"""1.5E100"""^^xsd:double', $typedLiteral->serialize());
    }

    public function testFloatTypedLiteralBoolValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(true, new PrefixedIri('xsd', 'float'));
    }

    public function testFloatTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('not-a-float', new PrefixedIri('xsd', 'float'));
    }

    public function testDoubleTypedLiteralBoolValue()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(true, new PrefixedIri('xsd', 'double'));
    }

    public function testDoubleTypedLiteralInvalidString()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('infinity', new PrefixedIri('xsd', 'double'));
    }

    // Integer subtype range validation

    public function testByteTypedLiteral()
    {
        $typedLiteral = new TypedLiteral(127, new PrefixedIri('xsd', 'byte'));
        $this->assertEquals('"""127"""^^xsd:byte', $typedLiteral->serialize());
        $typedLiteral = new TypedLiteral(-128, new PrefixedIri('xsd', 'byte'));
        $this->assertEquals('"""-128"""^^xsd:byte', $typedLiteral->serialize());
    }

    public function testByteTypedLiteralOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(200, new PrefixedIri('xsd', 'byte'));
    }

    public function testByteTypedLiteralUnderflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(-200, new PrefixedIri('xsd', 'byte'));
    }

    public function testShortTypedLiteralOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(40000, new PrefixedIri('xsd', 'short'));
    }

    public function testUnsignedByteTypedLiteralUnderflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(-1, new PrefixedIri('xsd', 'unsignedByte'));
    }

    public function testUnsignedByteTypedLiteralOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(256, new PrefixedIri('xsd', 'unsignedByte'));
    }

    public function testPositiveIntegerZero()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(0, new PrefixedIri('xsd', 'positiveInteger'));
    }

    public function testNegativeIntegerZero()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(0, new PrefixedIri('xsd', 'negativeInteger'));
    }

    public function testNonNegativeIntegerUnderflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(-1, new PrefixedIri('xsd', 'nonNegativeInteger'));
    }

    public function testNonPositiveIntegerOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(1, new PrefixedIri('xsd', 'nonPositiveInteger'));
    }

    public function testUnsignedLongTypedLiteralStringOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('18446744073709551616', new PrefixedIri('xsd', 'unsignedLong'));
    }

    // Semantic date/time validation

    public function testDateTypedLiteralInvalidMonth()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2024-13-01', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTypedLiteralInvalidDay()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2024-02-30', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTimeTypedLiteralInvalidDate()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2024-13-01T10:00', new PrefixedIri('xsd', 'dateTime'));
    }

    public function testDateTimeTypedLiteralInvalidHour()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2024-01-01T25:00', new PrefixedIri('xsd', 'dateTime'));
    }

    public function testTimeTypedLiteralInvalidHour()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('25:00:00', new PrefixedIri('xsd', 'time'));
    }

    public function testTimeTypedLiteralInvalidMinute()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('00:60:00', new PrefixedIri('xsd', 'time'));
    }

    public function testTimeEndOfDayMidnight()
    {
        // 24:00:00 is valid per XSD spec (end-of-day midnight).
        $typedLiteral = new TypedLiteral('24:00:00', new PrefixedIri('xsd', 'time'));
        $this->assertEquals('"""24:00:00"""^^xsd:time', $typedLiteral->serialize());
    }

    // Timezone offset validation

    public function testDateTypedLiteralMaxPositiveTimezone()
    {
        $typedLiteral = new TypedLiteral('2020-01-01+14:00', new PrefixedIri('xsd', 'date'));
        $this->assertEquals('"""2020-01-01+14:00"""^^xsd:date', $typedLiteral->serialize());
    }

    public function testDateTypedLiteralMaxNegativeTimezone()
    {
        $typedLiteral = new TypedLiteral('2020-01-01-14:00', new PrefixedIri('xsd', 'date'));
        $this->assertEquals('"""2020-01-01-14:00"""^^xsd:date', $typedLiteral->serialize());
    }

    public function testDateTypedLiteralTimezoneHourOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2020-01-01+15:00', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTypedLiteralTimezoneMinuteOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2020-01-01+00:60', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTypedLiteralTimezoneHour14NonZeroMinute()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2020-01-01+14:01', new PrefixedIri('xsd', 'date'));
    }

    public function testDateTimeTypedLiteralTimezoneHourOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2020-01-01T12:00+15:00', new PrefixedIri('xsd', 'dateTime'));
    }

    public function testTimeTypedLiteralTimezoneHourOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('12:00:00+15:00', new PrefixedIri('xsd', 'time'));
    }

    public function testTimeTypedLiteralTimezoneMinuteOverflow()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('12:00:00-00:60', new PrefixedIri('xsd', 'time'));
    }

    // xsd:decimal non-finite float rejection

    public function testDecimalTypedLiteralInfFloat()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(INF, new PrefixedIri('xsd', 'decimal'));
    }

    public function testDecimalTypedLiteralNegInfFloat()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(-INF, new PrefixedIri('xsd', 'decimal'));
    }

    public function testDecimalTypedLiteralNanFloat()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral(NAN, new PrefixedIri('xsd', 'decimal'));
    }

    // Proleptic Gregorian year 0 (1 BCE) leap year

    public function testDateTypedLiteralYear0LeapDay()
    {
        // XSD year 0000 = 1 BCE = astronomical year 0, which is a leap year.
        $typedLiteral = new TypedLiteral('0000-02-29', new PrefixedIri('xsd', 'date'));
        $this->assertEquals('"""0000-02-29"""^^xsd:date', $typedLiteral->serialize());
    }

    public function testDateTypedLiteralYear0InvalidLeapDay()
    {
        // XSD year -0001 = 2 BCE = astronomical year -1, not a leap year.
        $this->expectException(SparQlException::class);
        new TypedLiteral('-0001-02-29', new PrefixedIri('xsd', 'date'));
    }

    // Time: fractional seconds must invalidate 24:00:00

    public function testTimeEndOfDayMidnightWithFractionalSeconds()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('24:00:00.1', new PrefixedIri('xsd', 'time'));
    }

    public function testDateTimeEndOfDayMidnightWithFractionalSeconds()
    {
        $this->expectException(SparQlException::class);
        new TypedLiteral('2024-01-01T24:00:00.1', new PrefixedIri('xsd', 'dateTime'));
    }

    // compareIntegers: leading-zero normalization

    public function testByteTypedLiteralLeadingZeros()
    {
        // "000127" should be treated as 127, which is within xsd:byte range.
        $typedLiteral = new TypedLiteral('000127', new PrefixedIri('xsd', 'byte'));
        $this->assertEquals('"""000127"""^^xsd:byte', $typedLiteral->serialize());
    }

    public function testByteTypedLiteralLeadingZerosOverflow()
    {
        // "000200" = 200, which exceeds xsd:byte max of 127.
        $this->expectException(SparQlException::class);
        new TypedLiteral('000200', new PrefixedIri('xsd', 'byte'));
    }

    public function testNonNegativeIntegerNegativeZero()
    {
        // "-0" must be treated as 0, which satisfies xsd:nonNegativeInteger >= 0.
        $typedLiteral = new TypedLiteral('-0', new PrefixedIri('xsd', 'nonNegativeInteger'));
        $this->assertEquals('"""-0"""^^xsd:nonNegativeInteger', $typedLiteral->serialize());
    }

    public function testNegativeIntegerNegativeZero()
    {
        // "-0" = 0, which does not satisfy xsd:negativeInteger < 0.
        $this->expectException(SparQlException::class);
        new TypedLiteral('-0', new PrefixedIri('xsd', 'negativeInteger'));
    }
}
