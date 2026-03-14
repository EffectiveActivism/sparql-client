<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment\Values;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ValuesTest extends KernelTestCase
{
    const SERIALIZED_VALUES = 'SELECT ?headline ?commentCount WHERE { VALUES ( ?headline ?commentCount ) { ( """Lorem""" "2"^^xsd:integer ) ( """Ipsum""" UNDEF ) } . }';

    public function testValues()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $headlineVariable = new Variable('headline');
        $commentCountVariable = new Variable('commentCount');
        $headlineValue1 = new PlainLiteral('Lorem');
        $headlineValue2 = new PlainLiteral('Ipsum');
        $commentCountValue1 = new TypedLiteral(2);
        $values = new Values([$headlineVariable, $commentCountVariable], [[$headlineValue1, $commentCountValue1], [$headlineValue2, null]]);
        $statement = $sparQlClient
            ->select([$headlineVariable, $commentCountVariable])
            ->where([
                $values
            ]);
        $this->assertEquals(self::SERIALIZED_VALUES, $statement->toQuery());
        $this->assertEquals([
            $headlineVariable,
            $commentCountVariable,
            $headlineValue1,
            $commentCountValue1,
            $headlineValue2
        ], $values->getTerms());
        $this->assertEquals([
            $headlineVariable,
            $commentCountVariable,
            $headlineValue1,
            $commentCountValue1,
            $headlineValue2
        ], $values->toArray());
        $this->assertEquals([$headlineVariable, $commentCountVariable], $values->getVariables());
    }

    public function testValuesExceptions()
    {
        $headlineVariable = new Variable('headline');
        $commentCountVariable = new Variable('commentCount');
        $headlineValue1 = new PlainLiteral('Lorem');
        $headlineValue2 = new PlainLiteral('Ipsum');
        $commentCountValue1 = new TypedLiteral(2);
        $threwException = false;
        // Test with invalid variables.
        try {
            new Values([$headlineValue1, $commentCountVariable], [[$headlineValue1, $commentCountValue1], [$headlineValue2, null]]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        // Test with invalid values.
        try {
            new Values([$headlineVariable, $commentCountVariable], [[$headlineVariable, $commentCountValue1], [$headlineValue2, null]]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        $threwException = false;
        // Test with invalid dimensions.
        try {
            new Values([$headlineVariable, $commentCountVariable], [[$headlineValue1, $commentCountValue1], [$headlineValue2]]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
