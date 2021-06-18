<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Optionally;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Service\Service;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ServiceTest extends KernelTestCase
{
    const SERIALIZED_VALUE = 'SERVICE bds:search { ?object bds:search "Lorem" . }';

    public function testService()
    {
        $service = new PrefixedIri('bds', 'search');
        $object = new Variable('object');
        $predicate = new PrefixedIri('bds', 'search');
        $searchTerm = new PlainLiteral('Lorem');
        $triple = new Triple($object, $predicate, $searchTerm);
        $serviceClause = new Service($service, [$triple]);
        $this->assertEquals(self::SERIALIZED_VALUE, $serviceClause->serialize());
        $this->assertEquals([$service, $object, $predicate, $searchTerm], $serviceClause->getTerms());
        $this->assertEquals([$triple], $serviceClause->toArray());
    }

    public function testInvalidFilterExists()
    {
        $this->expectException(SparQlException::class);
        new Service(new PrefixedIri('bds', 'search'), [
            'invalid pattern argument',
        ]);
    }
}
