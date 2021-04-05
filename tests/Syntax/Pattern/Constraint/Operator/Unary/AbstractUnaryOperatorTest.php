<?php


namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Unary;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\AbstractUnaryOperator;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractUnaryOperatorTest extends KernelTestCase
{
    const IRI = 'urn:uuid:a8ea08b2-95f7-11eb-aa9c-23071bcbf225';

    public function testAbstractUnaryOperator()
    {
        $term = new Iri(self::IRI);
        $class = new class($term) extends AbstractUnaryOperator {
            public function __construct(TermInterface $term)
            {
                $this->expression = $term;
            }

            public function serialize(): string
            {
                return '';
            }
        };
        $this->assertEquals($term, $class->getExpression());
    }
}
