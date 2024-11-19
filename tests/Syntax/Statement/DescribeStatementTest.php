<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\Asc;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\DescribeStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DescribeStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const DESCRIBE_STATEMENT1 = 'DESCRIBE ?subject WHERE { ?subject <http://schema.org/headline> """Lorem Ipsum""" . } ORDER BY ASC( ?subject ) LIMIT 1 OFFSET 1';

    const DESCRIBE_STATEMENT2 = 'DESCRIBE <http://schema.org/headline>';

    public function testDescribeStatement()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement1 = new DescribeStatement([$subjectVariable]);
        $statement1->where([$triple]);
        $statement1->limit(1);
        $statement1->offset(1);
        $statement1->orderBy([new Asc($subjectVariable)]);
        $this->assertEquals(self::DESCRIBE_STATEMENT1, $statement1->toQuery());
        $this->assertEquals([$subjectVariable], $statement1->getResources());
        $statement2 = new DescribeStatement([$predicate]);
        $this->assertEquals(self::DESCRIBE_STATEMENT2, $statement2->toQuery());
        $this->assertEquals([$predicate], $statement2->getResources());
    }

    public function testDescribeExceptions()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        // Test statement with non-variable/non-iri.
        $threwException = false;
        try {
            new DescribeStatement([$object]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable not included in where clause.
        $unknownSubjectVariable = new Variable('unknown');
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $threwException = false;
        $statement = new DescribeStatement([$subjectVariable]);
        $statement
            ->where([$triple]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test limit.
        $threwException = false;
        $statement = new DescribeStatement([$subjectVariable]);
        try {
            $statement->limit(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test offset.
        $threwException = false;
        $statement = new DescribeStatement([$subjectVariable]);
        try {
            $statement->offset(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test order by.
        $statement = new DescribeStatement([$subjectVariable]);
        try {
            $statement->orderBy([
                'invalid',
            ]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
