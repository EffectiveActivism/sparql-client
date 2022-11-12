<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\AskStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AskStatementTest extends KernelTestCase
{
    const SUBJECT_URI = 'urn:uuid:606d5596-a8ec-11eb-8430-bb55757c9ada';

    const ASK_STATEMENT = 'ASK { <urn:uuid:606d5596-a8ec-11eb-8430-bb55757c9ada> <http://schema.org/headline> """Lorem Ipsum""" . }';

    public function testAskStatement()
    {
        $subject = new Iri(self::SUBJECT_URI);
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral("Lorem Ipsum");
        $triple = new Triple($subject, $predicate, $object);
        $statement = new AskStatement([]);
        $statement->where([$triple]);
        $this->assertEquals(self::ASK_STATEMENT, $statement->toQuery());
    }
}
