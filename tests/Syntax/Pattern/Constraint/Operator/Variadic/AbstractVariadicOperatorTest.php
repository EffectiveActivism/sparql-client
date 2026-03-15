<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint\Operator\Variadic;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\AbstractVariadicOperator;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AbstractVariadicOperatorTest extends KernelTestCase
{
    const IRI_1 = 'urn:uuid:a8ea08b2-95f7-11eb-aa9c-23071bcbf225';

    const IRI_2 = 'urn:uuid:1c7eff9e-95f8-11eb-953a-d3fdc6c1a8eb';

    public function testAbstractVariadicOperator()
    {
        $term1 = new Iri(self::IRI_1);
        $term2 = new Iri(self::IRI_2);
        $class = new class($term1, $term2) extends AbstractVariadicOperator {
            public function __construct(TermInterface ...$expressions)
            {
                $this->expressions = $expressions;
            }

            public function serialize(): string
            {
                return '';
            }
        };
        $this->assertEquals([$term1, $term2], $class->getExpressions());
    }
}
