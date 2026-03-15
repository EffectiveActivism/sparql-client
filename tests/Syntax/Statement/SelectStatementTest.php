<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\Asc;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Aggregate\Count;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\GreaterThan;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression\SelectExpression;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const SELECT_STATEMENT = 'SELECT ?subject WHERE { ?subject <http://schema.org/headline> """Lorem Ipsum""" . } ORDER BY ASC( ?subject )';

    public function testSelectStatement()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->orderBy([new Asc($subjectVariable)]);
        $this->assertEquals(self::SELECT_STATEMENT, $statement->toQuery());
        $this->assertEquals([$subjectVariable], $statement->getVariables());
    }

    public function testSelectExceptions()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        // Test statement with non-variable.
        $threwException = false;
        try {
            new SelectStatement([$predicate]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable not included in where clause.
        $unknownSubjectVariable = new Variable('unknown');
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $threwException = false;
        $statement = new SelectStatement([$subjectVariable]);
        $statement
            ->where([$triple]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with where clause not included.
        $threwException = false;
        $statement = new SelectStatement([$subjectVariable]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test limit.
        $threwException = false;
        $statement = new SelectStatement([$subjectVariable]);
        try {
            $statement->limit(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test offset.
        $threwException = false;
        $statement = new SelectStatement([$subjectVariable]);
        try {
            $statement->offset(-1);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test order by.
        $statement = new SelectStatement([$subjectVariable]);
        try {
            $statement->orderBy([
                'invalid',
            ]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with undeclared prefix in conditions.
        $threwException = false;
        $undeclaredPredicate = new PrefixedIri('unknown', 'headline');
        $triple = new Triple($subjectVariable, $undeclaredPredicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testDistinct()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->distinct();
        $this->assertStringContainsString('SELECT DISTINCT', $statement->toQuery());
    }

    public function testReduced()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->reduced();
        $this->assertStringContainsString('SELECT REDUCED', $statement->toQuery());
    }

    public function testDistinctAndReducedException()
    {
        $statement = new SelectStatement([new Variable('subject')]);
        $statement->distinct();
        $threwException = false;
        try {
            $statement->reduced();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testReducedAndDistinctException()
    {
        $statement = new SelectStatement([new Variable('subject')]);
        $statement->reduced();
        $threwException = false;
        try {
            $statement->distinct();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testGroupBy()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->groupBy([$subjectVariable]);
        $this->assertStringContainsString('GROUP BY ?subject', $statement->toQuery());
    }

    public function testGroupByWithOperator()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->groupBy([new Count($subjectVariable)]);
        $this->assertStringContainsString('GROUP BY COUNT(?subject)', $statement->toQuery());
    }

    public function testGroupByInvalidExpression()
    {
        $statement = new SelectStatement([new Variable('subject')]);
        $threwException = false;
        try {
            $statement->groupBy(['invalid']);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }

    public function testHaving()
    {
        $subjectVariable = new Variable('subject');
        $countVariable = new Variable('count');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $countExpr = new SelectExpression(new Count($subjectVariable), $countVariable);
        $statement = new SelectStatement([$countExpr]);
        $statement->where([$triple]);
        $countOp = new Count($subjectVariable);
        $havingOp = new GreaterThan($countOp, $subjectVariable);
        $statement->having($havingOp);
        $this->assertStringContainsString('HAVING(', $statement->toQuery());
    }

    public function testSelectExpression()
    {
        $subjectVariable = new Variable('subject');
        $countVariable = new Variable('count');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem Ipsum');
        $triple = new Triple($subjectVariable, $predicate, $object);
        $countExpr = new SelectExpression(new Count($subjectVariable), $countVariable);
        $statement = new SelectStatement([$countExpr]);
        $statement->where([$triple]);
        $query = $statement->toQuery();
        $this->assertStringContainsString('( COUNT(?subject) AS ?count )', $query);
    }
}
