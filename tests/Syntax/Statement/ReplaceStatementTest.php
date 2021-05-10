<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\ReplaceStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ReplaceStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const REPLACE_STATEMENT = 'DELETE { <urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639> <http://schema.org/headline> "Lorem Ipsum" } INSERT { <urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639> <http://schema.org/headline> "Lorem Ipsum" } WHERE { <urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639> <http://schema.org/headline> "Lorem Ipsum" . }';

    public function testReplaceStatement()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $statement = new ReplaceStatement($triple);
        $statement->with($triple);
        $statement->where([$triple]);
        $this->assertEquals(self::REPLACE_STATEMENT, $statement->toQuery());
    }

    public function testReplaceExceptions()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        // Test statement with missing 'with' clause.
        $threwException = false;
        try {
            $statement = new ReplaceStatement($triple);
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with unknown IRI prefix.
        $unknownPredicate = new PrefixedIri('unknown', 'headline');
        $triple = new Triple($subject, $unknownPredicate, $object);
        $threwException = false;
        try {
            new ReplaceStatement($triple);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with unknown IRI prefix in 'with' clause.
        $triple1 = new Triple($subject, $predicate, $object);
        $triple2 = new Triple($subject, $unknownPredicate, $object);
        $threwException = false;
        try {
            $statement = new ReplaceStatement($triple1);
            $statement->with($triple2);
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable not included in where clause.
        $unknownSubjectVariable = new Variable('subject');
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $triple2 = new Triple($subject, $predicate, $object);
        $threwException = false;
        $statement = new ReplaceStatement($triple);
        $statement
            ->with($triple)
            ->where([$triple2]);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable without where clause.
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $threwException = false;
        $statement = new ReplaceStatement($triple);
        $statement->with($triple);
        try {
            $statement->toQuery();
        } catch (SparQlException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
