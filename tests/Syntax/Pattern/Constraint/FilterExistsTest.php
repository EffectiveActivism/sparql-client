<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\FilterExists;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilterExistsTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX owl: <http://www.w3.org/2002/07/owl#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> PREFIX schema: <http://schema.org/>';

    const SERIALIZED_FILTER = self::NAMESPACES . ' SELECT ?subject WHERE { ?subject schema:headline "lorem" . FILTER EXISTS { ?subject schema:identifier "b5e80b02-9081-11eb-af19-3b334e80a450" . } . }';

    const SERIALIZED_FILTER_NESTED = self::NAMESPACES . ' SELECT ?subject WHERE { ?subject schema:headline "lorem" . FILTER EXISTS { FILTER EXISTS { ?subject schema:identifier "b5e80b02-9081-11eb-af19-3b334e80a450" . } . } . }';

    public function testFilterExists()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subjectVariable = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $filterPredicate = new PrefixedIri('schema', 'identifier');
        $object = new PlainLiteral('lorem');
        $filterObject = new PlainLiteral('b5e80b02-9081-11eb-af19-3b334e80a450');
        $filter = new FilterExists([new Triple($subjectVariable, $filterPredicate, $filterObject)]);
        $statement = $sparQlClient
            ->select([$subjectVariable])
            ->where([
                new Triple($subjectVariable, $predicate, $object),
                $filter
            ]);
        $this->assertEquals(self::SERIALIZED_FILTER, $statement->toQuery());
    }

    public function testFilterExistsNested()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subjectVariable = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $filterPredicate = new PrefixedIri('schema', 'identifier');
        $object = new PlainLiteral('lorem');
        $filterObject = new PlainLiteral('b5e80b02-9081-11eb-af19-3b334e80a450');
        $filter = new FilterExists([
            new FilterExists([
                new Triple($subjectVariable, $filterPredicate, $filterObject)
            ]),
        ]);
        $statement = $sparQlClient
            ->select([$subjectVariable])
            ->where([
                new Triple($subjectVariable, $predicate, $object),
                $filter
            ]);
        $this->assertEquals(self::SERIALIZED_FILTER_NESTED, $statement->toQuery());
        $this->assertCount(3, $filter->toArray());
    }

    public function testInvalidFilterExists()
    {
        $this->expectException(InvalidArgumentException::class);
        new FilterExists([
            'invalid filter argument',
        ]);
    }
}
