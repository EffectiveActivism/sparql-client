<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\DeleteStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class DeleteStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639';

    const SUBJECT_URI_2 = 'urn:uuid:958cf9aa-918d-11eb-82e0-8774f11a1054';

    const DELETE_STATEMENT = 'DELETE { <urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639> <http://schema.org/headline> "Lorem Ipsum" } WHERE { <urn:uuid:89e2f582-918d-11eb-b6ff-1f71a7aa4639> <http://schema.org/headline> "Lorem Ipsum" . }';

    public function testDeleteStatement()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $statement = new DeleteStatement($triple);
        $statement->where([$triple]);
        $this->assertEquals(self::DELETE_STATEMENT, $statement->toQuery());
    }

    public function testDeleteExceptions()
    {
        // Test statement with unknown IRI prefix.
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $unknownPredicate = new PrefixedIri('unknown', 'headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $unknownPredicate, $object);
        $threwException = false;
        try {
            new DeleteStatement($triple);
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable not included in where clause.
        $unknownSubjectVariable = new Variable('subject');
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $triple2 = new Triple($subject, $predicate, $object);
        $threwException = false;
        $statement = new DeleteStatement($triple);
        $statement->where([$triple2]);
        try {
            $statement->toQuery();
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
        // Test statement with variable without where clause.
        $triple = new Triple($unknownSubjectVariable, $predicate, $object);
        $threwException = false;
        $statement = new DeleteStatement($triple);
        try {
            $statement->toQuery();
        } catch (InvalidArgumentException) {
            $threwException = true;
        }
        $this->assertTrue($threwException);
    }
}
