<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Constraint;

use EffectiveActivism\SparQlClient\Client\SparQlClientInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment\Bind;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\Binary\Multiply;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Tests\Environment\TestKernel;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class BindTest extends KernelTestCase
{
    const NAMESPACES = 'PREFIX rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> PREFIX rdfs: <http://www.w3.org/2000/01/rdf-schema#> PREFIX owl: <http://www.w3.org/2002/07/owl#> PREFIX skos: <http://www.w3.org/2004/02/skos/core#> PREFIX xsd: <http://www.w3.org/2001/XMLSchema#> PREFIX schema: <http://schema.org/>';

    const SERIALIZED_BIND = self::NAMESPACES . ' SELECT ?subject ?processedCommentCountVariable WHERE { ?subject schema:commentCount ?commentCount . BIND ("2"^^xsd:integer * ?commentCount ) AS ?processedCommentCountVariable . }';

    public function testBind()
    {
        $kernel = new TestKernel('test', true);
        $kernel->boot();
        /** @var SparQlClientInterface $sparQlClient */
        $sparQlClient = $kernel->getContainer()->get(SparQlClientInterface::class);
        $subjectVariable = new Variable('subject');
        $predicate = new PrefixedIri('schema', 'commentCount');
        $commentCountVariable = new Variable('commentCount');
        $processedCommentCountVariable = new Variable('processedCommentCountVariable');
        $bind = new Bind(new Multiply(new TypedLiteral(2), $commentCountVariable), $processedCommentCountVariable);
        $statement = $sparQlClient
            ->select([$subjectVariable, $processedCommentCountVariable])
            ->where([
                new Triple($subjectVariable, $predicate, $commentCountVariable),
                $bind
            ]);
        $this->assertEquals(self::SERIALIZED_BIND, $statement->toQuery());
        $this->assertEquals([$processedCommentCountVariable], $bind->toArray());
        $bind = new Bind(new Triple($subjectVariable, $predicate, $commentCountVariable), $processedCommentCountVariable);
        $this->assertEquals([
            $processedCommentCountVariable,
            $subjectVariable,
            $predicate,
            $commentCountVariable
        ], $bind->getTerms());
    }
}
