<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\Asc;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const SELECT_STATEMENT = 'SELECT ?subject WHERE { ?subject <http://schema.org/headline> "Lorem Ipsum" . } ORDER BY ASC( ?subject )';

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
    }
}
