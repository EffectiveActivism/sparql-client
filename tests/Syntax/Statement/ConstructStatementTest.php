<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\ConstructStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ConstructStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const CONSTRUCT_STATEMENT = 'CONSTRUCT { ?subject <http://schema.org/headline> "Lorem Ipsum" .  } WHERE { ?subject <http://schema.org/headline> "Lorem Ipsum" . }';

    public function testConstructStatement()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subjectVariable, $predicate, $object);
        $statement = new ConstructStatement([$triple]);
        $statement->where([$triple]);
        $this->assertEquals(self::CONSTRUCT_STATEMENT, $statement->toQuery());
        $this->assertEquals([$triple], $statement->getTriplesToConstruct());
    }

    public function testConstructExceptions()
    {
        $subjectVariable = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subjectVariable, $predicate, $object);
        // Test statement with non-triple.
        $threwException = false;
        try {
            new ConstructStatement([$subjectVariable]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with term without valid namespace.
        $threwException = false;
        try {
            new ConstructStatement([$triple]);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable not included in where clause.
        $unknownSubjectVariable = new Variable('unknown');
        $subject = new Iri('urn:uuid:4e940e98-a37c-11eb-8933-5be6fbea2e11');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $tripleWithUnknownSubjectVariable = new Triple($unknownSubjectVariable, $predicate, $object);
        $threwException = false;
        $statement = new ConstructStatement([$tripleWithUnknownSubjectVariable]);
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
        $statement = new ConstructStatement([$triple]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
