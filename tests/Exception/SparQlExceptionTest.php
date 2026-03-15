<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Tests\Exception;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use PHPUnit\Framework\TestCase;

class SparQlExceptionTest extends TestCase
{
    /**
     * @covers \EffectiveActivism\SparQlClient\Exception\SparQlException
     */
    public function testSparQlExceptionGetters(): void
    {
        $exception = new SparQlException(
            message: 'Something went wrong',
            code: 0,
            previous: null,
            statusCode: 400,
            responseBody: 'Bad request',
            query: 'SELECT ?s WHERE { ?s ?p ?o }',
        );
        $this->assertSame(400, $exception->getStatusCode());
        $this->assertSame('Bad request', $exception->getResponseBody());
        $this->assertSame('SELECT ?s WHERE { ?s ?p ?o }', $exception->getQuery());
    }

    /**
     * @covers \EffectiveActivism\SparQlClient\Exception\SparQlException
     */
    public function testSparQlExceptionNullableGetters(): void
    {
        $exception = new SparQlException('Error');
        $this->assertNull($exception->getStatusCode());
        $this->assertNull($exception->getResponseBody());
        $this->assertNull($exception->getQuery());
    }
}
