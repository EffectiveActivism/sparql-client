<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\SequencePath;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SequencePathTest extends KernelTestCase
{
    const SUBJECT_1 = 'urn:uuid:6b14e3e6-8705-11eb-8a61-039a55e6ea7c';
    const SUBJECT_2 = 'urn:uuid:7330ff38-8705-11eb-b7c9-db680063fa4d';
    const SUBJECT_3 = 'urn:uuid:73623404-8705-11eb-bd4c-87358db33150';

    public function testSequencePath()
    {
        $subject1 = new Iri(self::SUBJECT_1);
        $subject2 = new Iri(self::SUBJECT_2);
        $subject3 = new Iri(self::SUBJECT_3);
        $sequencePath = new SequencePath($subject1, $subject2);
        $this->assertEquals(sprintf('<%s> / <%s>', self::SUBJECT_1, self::SUBJECT_2), $sequencePath->serialize());
        $doubleSequencePath = new SequencePath($subject3, $sequencePath);
        $this->assertEquals(sprintf('<%s> / (<%s> / <%s>)', self::SUBJECT_3, self::SUBJECT_1, self::SUBJECT_2), $doubleSequencePath->serialize());
    }
}
