<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AlternativePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class AlternativePathTest extends KernelTestCase
{
    const SUBJECT_1 = 'urn:uuid:9a7d7afa-8708-11eb-9048-ff38db415f43';
    const SUBJECT_2 = 'urn:uuid:9bfdffee-8708-11eb-a8d2-575c4f8ca101';
    const SUBJECT_3 = 'urn:uuid:9c2ed10a-8708-11eb-aac4-f783f3a172d3';

    public function testAlternativePath()
    {
        $subject1 = new Iri(self::SUBJECT_1);
        $subject2 = new Iri(self::SUBJECT_2);
        $subject3 = new Iri(self::SUBJECT_3);
        $alternativePath = new AlternativePath($subject1, $subject2);
        $this->assertEquals(sprintf('<%s> | <%s>', self::SUBJECT_1, self::SUBJECT_2), $alternativePath->serialize());
        $doubleAlternativePath = new AlternativePath($subject3, $alternativePath);
        $this->assertEquals(sprintf('<%s> | (<%s> | <%s>)', self::SUBJECT_3, self::SUBJECT_1, self::SUBJECT_2), $doubleAlternativePath->serialize());
    }
}
