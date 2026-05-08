<?php

namespace EffectiveActivism\SparQlClient\Tests\Syntax\Pattern\Subquery;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\Triple;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class SubqueryTest extends KernelTestCase
{
    const SERIALIZED_VALUE = '{ SELECT ?subject WHERE { ?subject <http://schema.org/headline> """Lorem""" . } }';

    public function testSubquery()
    {
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $statement = new SelectStatement([$subject]);
        $statement->where([$triple]);
        $subquery = new Subquery($statement);
        $this->assertEquals(self::SERIALIZED_VALUE, $subquery->serialize());
        $this->assertEquals([$subject, $predicate, $object], $subquery->getTerms());
        $this->assertEquals([$subject, $predicate, $object], $subquery->toArray());
    }

    /**
     * Regression: prologue (BASE/PREFIX) and dataset clauses (FROM/FROM NAMED)
     * are illegal inside a group graph pattern, so a Subquery must not leak
     * them from the inner statement's full toQuery() output.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testSubqueryOmitsPrologueAndDatasetClause()
    {
        $subject = new Variable('subject');
        $predicate = new Iri('http://schema.org/headline');
        $object = new PlainLiteral('Lorem');
        $triple = new Triple($subject, $predicate, $object);
        $statement = new SelectStatement([$subject]);
        $statement
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->withBase('http://example.org/')
            ->from(new Iri('http://example.org/graph'))
            ->fromNamed(new Iri('http://example.org/named'))
            ->where([$triple]);
        $serialized = (new Subquery($statement))->serialize();
        $this->assertStringNotContainsString('PREFIX', $serialized);
        $this->assertStringNotContainsString('BASE', $serialized);
        $this->assertStringNotContainsString('FROM', $serialized);
        $this->assertEquals(self::SERIALIZED_VALUE, $serialized);
    }

    /**
     * Inner-statement namespaces propagate to the outer query's prologue,
     * so callers don't have to duplicate `withNamespaces()` on the outer.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testSubqueryPropagatesInnerNamespacesToOuterPrologue()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $inner = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'headline'), $object)]);
        $outer = (new SelectStatement([$subject]))
            ->where([new Subquery($inner)]);
        $query = $outer->toQuery();
        $this->assertStringContainsString('PREFIX schema: <http://schema.org/>', $query);
        $this->assertStringContainsString('schema:headline', $query);
    }

    /**
     * Namespaces from a subquery nested inside another subquery bubble up
     * to the outermost statement's prologue.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testNestedSubqueryNamespacesBubbleUp()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $deep = (new SelectStatement([$subject]))
            ->withNamespaces(['foaf' => 'http://xmlns.com/foaf/0.1/'])
            ->where([new Triple($subject, new PrefixedIri('foaf', 'name'), $object)]);
        $middle = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Subquery($deep)]);
        $outer = (new SelectStatement([$subject]))
            ->where([new Subquery($middle)]);
        $query = $outer->toQuery();
        $this->assertStringContainsString('PREFIX foaf: <http://xmlns.com/foaf/0.1/>', $query);
        $this->assertStringContainsString('PREFIX schema: <http://schema.org/>', $query);
    }

    /**
     * The outer's own `withNamespaces()` continues to work alongside
     * propagation: same prefix bound to the same URL on both sides is fine.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testMatchingNamespaceOnInnerAndOuterIsAccepted()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $inner = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'headline'), $object)]);
        $outer = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Subquery($inner)]);
        $this->assertStringContainsString('PREFIX schema: <http://schema.org/>', $outer->toQuery());
    }

    /**
     * Same prefix bound to different URLs across inner and outer is a user
     * error: refuse to silently pick one.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testConflictingNamespaceBetweenInnerAndOuterThrows()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $inner = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'headline'), $object)]);
        $outer = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://example.org/wrong/'])
            ->where([new Subquery($inner)]);
        $this->expectException(SparQlException::class);
        $this->expectExceptionMessage('Conflicting namespace declarations for prefix "schema"');
        $outer->toQuery();
    }

    /**
     * Two sibling subqueries declaring the same prefix with different URLs
     * also conflict.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testConflictingNamespaceBetweenSiblingSubqueriesThrows()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $left = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'headline'), $object)]);
        $right = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://example.org/other/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'name'), $object)]);
        $outer = (new SelectStatement([$subject]))
            ->where([new Subquery($left), new Subquery($right)]);
        $this->expectException(SparQlException::class);
        $this->expectExceptionMessage('Conflicting namespace declarations for prefix "schema"');
        $outer->toQuery();
    }

    /**
     * A prefix used inside the subquery but declared only on the inner
     * statement no longer raises 'Prefix … is not defined' on the outer.
     *
     * @covers \EffectiveActivism\SparQlClient\Syntax\Pattern\Subquery\Subquery
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\AbstractStatement
     * @covers \EffectiveActivism\SparQlClient\Syntax\Statement\SelectStatement
     */
    public function testValidatePrefixesAcceptsPrefixDeclaredOnlyOnInner()
    {
        $subject = new Variable('subject');
        $object = new Variable('object');
        $inner = (new SelectStatement([$subject]))
            ->withNamespaces(['schema' => 'http://schema.org/'])
            ->where([new Triple($subject, new PrefixedIri('schema', 'headline'), $object)]);
        $outer = (new SelectStatement([$subject]))
            ->where([new Subquery($inner)]);
        $outer->toQuery();
        $this->assertArrayHasKey('schema', $outer->getNamespaces());
    }
}
