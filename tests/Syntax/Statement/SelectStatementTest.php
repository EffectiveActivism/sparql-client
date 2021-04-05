<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\Triple;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SelectStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const SELECT_STATEMENT = 'SELECT ?subject WHERE { ?subject <http://schema.org/headline> "Lorem Ipsum" . OPTIONAL {?subject <http://schema.org/headline> "Lorem Ipsum"} .}';

    public function testSelectStatement()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new SelectStatement([$subjectVariable]);
        $statement->where([$triple]);
        $statement->optionallyWhere([$triple]);
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
        } catch (InvalidArgumentException) {
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
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with where clause not included.
        $threwException = false;
        $statement = new SelectStatement([$subjectVariable]);
        try {
            $statement->toQuery();
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
