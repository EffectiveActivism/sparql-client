<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Filter;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Equal;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Trinary\Regex;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Unary\Bound;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Variadic\In;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class FilterTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX schema: <http://schema.org/>';

    const SERIALIZED_FILTER = self::NAMESPACES . ' SELECT ?subject WHERE { ?subject schema:headline """lorem""" . FILTER(BOUND(?subject)) . FILTER("""lorem""" = """ipsum""") . FILTER(REGEX("""lorem""","""ipsum""","""foo""")) . }';

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
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([
                new Triple($subjectVariable, $predicate, $object1),
                $filter1,
                $filter2,
                $filter3,
            ]);
        $this->assertEquals(self::SERIALIZED_FILTER, $statement->toQuery());
        $this->assertEquals([$subjectVariable], $filter1->getTerms());
        $this->assertEquals([$subjectVariable], $filter1->toArray());
        // Binary: left and right expressions returned.
        $this->assertEquals([$object1, $object2], $filter2->getTerms());
        // Trinary with 3 args: left, middle, and right expressions returned.
        $this->assertEquals([$object1, $object2, $object3], $filter3->getTerms());
    }

    public function testGetTermsTrinaryTwoArgs()
    {
        $object1 = new PlainLiteral('lorem');
        $object2 = new PlainLiteral('ipsum');
        $filter = new Filter(new Regex($object1, $object2));
        // Trinary with 2 args: only left and middle returned (no null right).
        $this->assertEquals([$object1, $object2], $filter->getTerms());
    }

    public function testAggregateInFilterThrows()
    {
        $this->expectException(SparQlException::class);
        new Filter(new Count(new Variable('subject')));
    }

    public function testGetTermsVariadic()
    {
        $subject = new Variable('subject');
        $a = new PlainLiteral('lorem');
        $b = new PlainLiteral('ipsum');
        $filter = new Filter(new In($subject, $a, $b));
        // Variadic: all expressions returned (subject + each additional).
        $this->assertEquals([$subject, $a, $b], $filter->getTerms());
    }
}
