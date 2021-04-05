<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Optionally;

use EffectiveActivism\SparQlClient\Syntax\Pattern\Optionally\Optionally;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class OptionallyTest extends KernelTestCase
{
    const IRI = 'urn:uuid:b40fe72e-95f8-11eb-bca8-5ffd58524f9a';

    const SERIALIZED_VALUE = 'OPTIONAL { <urn:uuid:b40fe72e-95f8-11eb-bca8-5ffd58524f9a> schema:headline "Lorem" . }';

    public function testOptionally()
    {
        $subject = new Iri(self::IRI);
        $predicate = new PrefixedIri('schema', 'headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $optionalClause = new Optionally([$triple]);
        $this->assertEquals(self::SERIALIZED_VALUE, $optionalClause->serialize());
        $this->assertEquals([$subject, $predicate, $object], $optionalClause->toArray());
    }

    public function testInvalidFilterExists()
    {
        $this->expectException(InvalidArgumentException::class);
        new Optionally([
            'invalid pattern argument',
        ]);
    }
}
