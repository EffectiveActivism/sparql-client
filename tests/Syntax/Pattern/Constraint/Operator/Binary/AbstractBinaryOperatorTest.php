<?php


namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Binary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\AbstractBinaryOperator;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractBinaryOperatorTest extends KernelTestCase
{
    const IRI_1 = 'urn:uuid:21cc1450-95f8-11eb-9460-0f57437ab537';

    const IRI_2 = 'urn:uuid:1c7eff9e-95f8-11eb-953a-d3fdc6c1a8eb';

    public function testAbstractUnaryOperator()
    {
        $term1 = new Iri(self::IRI_1);
        $term2 = new Iri(self::IRI_2);
        $class = new class($term1, $term2) extends AbstractBinaryOperator {
            public function __construct(TermInterface $left, TermInterface $right)
            {
                $this->leftExpression = $left;
                $this->rightExpression = $right;
            }

            public function serialize(): string
            {
                return '';
            }
        };
        $this->assertEquals($term1, $class->getLeftExpression());
        $this->assertEquals($term2, $class->getRightExpression());
    }
}
