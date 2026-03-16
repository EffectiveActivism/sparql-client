<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Result;

use EffectiveActivism\SparQlClient\Result\AskResult;
use EffectiveActivism\SparQlClient\Result\ConstructResult;
use EffectiveActivism\SparQlClient\Result\DescribeResult;
use EffectiveActivism\SparQlClient\Result\SelectResult;
use EffectiveActivism\SparQlClient\Result\UpdateResult;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use PHPUnit\Framework\TestCase;

class ResultTest extends TestCase
{
    /**
     * @covers \EffectiveActivism\SparQlClient\Result\AskResult
     */
    public function testAskResult(): void
    {
        $result = new AskResult(true);
        $this->assertTrue($result->getAnswer());
        $result = new AskResult(false);
        $this->assertFalse($result->getAnswer());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Result\SelectResult
     */
    public function testSelectResult(): void
    {
        $rows = [['subject' => new Iri('urn:uuid:test')]];
        $result = new SelectResult($rows);
        $this->assertSame($rows, $result->getRows());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Result\ConstructResult
     */
    public function testConstructResult(): void
    {
        $triple = new Triple(
            new Iri('urn:uuid:test'),
            new PrefixedIri('schema', 'name'),
            new Variable('object')
        );
        $result = new ConstructResult([$triple]);
        $this->assertSame([$triple], $result->getTriples());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Result\DescribeResult
     */
    public function testDescribeResult(): void
    {
        $triple = new Triple(
            new Iri('urn:uuid:test'),
            new PrefixedIri('schema', 'name'),
            new Variable('object')
        );
        $result = new DescribeResult([$triple]);
        $this->assertSame([$triple], $result->getTriples());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Result\UpdateResult
     */
    public function testUpdateResult(): void
    {
        $result = new UpdateResult(200, 'response body');
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('response body', $result->getBody());
    }
}
