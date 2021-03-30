<?php

namespace EffectiveActivism\SparQlClient\Tests\Constraint;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Filter;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Binary\Equal;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Trinary\Regex;
use EffectiveActivism\SparQlClient\Syntax\Constraint\Operator\Unary\Bound;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilterTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX owl: <http://www.w3.org/2002/07/owl#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> PREFIX schema: <http://schema.org/>';

    const SERIALIZED_FILTER = self::NAMESPACES . ' SELECT ?subject WHERE { ?subject schema:headline "lorem" . FILTER(BOUND(?subject)) . FILTER("lorem" = "ipsum") . FILTER(REGEX("lorem","ipsum","foo")) . }';

    public function testFilter()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subjectVariable = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object1 = new PlainLiteral('lorem');
        $object2 = new PlainLiteral('ipsum');
        $object3 = new PlainLiteral('foo');
        $filter1 = new Filter(new Bound($subjectVariable));
        $filter2 = new Filter(new Equal($object1, $object2));
        $filter3 = new Filter(new Regex($object1, $object2, $object3));
        $statement = $sparQlClient
            ->select([$subjectVariable])
            ->where([
                new Triple($subjectVariable, $predicate, $object1),
                $filter1,
                $filter2,
                $filter3,
            ]);
        $this->assertEquals(self::SERIALIZED_FILTER, $statement->toQuery());
    }
}
