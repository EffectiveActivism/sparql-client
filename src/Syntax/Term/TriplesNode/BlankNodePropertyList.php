<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\RdfType;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AbstractPath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

/**
 * A blank node property list, serialised as `[ p1 o1 ; p2 o2 … ]`.
 *
 * The list is constructed from predicate-object pairs, each given as a
 * two-element `[$predicate, $object]` array. As in a triple, a predicate
 * must be an IRI, a path, or a variable (never a literal, a blank node, or
 * another triples node), and an object is a GraphNode (a variable, a graph
 * term, or a nested triples node).
 *
 * @see https://www.w3.org/TR/sparql11-query/#rBlankNodePropertyList
 */
class BlankNodePropertyList extends AbstractTerm implements TriplesNodeInterface
{
    /** @var array<int, array{0: TermInterface, 1: TermInterface}> */
    protected array $pairs;

    /**
     * @throws SparQlException
     */
    public function __construct(array $pairs)
    {
        $this->applyPairs($pairs);
    }

    /**
     * @throws SparQlException
     */
    private function applyPairs(array $pairs): void
    {
        if (empty($pairs)) {
            throw new SparQlException('BlankNodePropertyList requires at least one predicate-object pair');
        }
        foreach ($pairs as $pair) {
            if (!is_array($pair) || count($pair) !== 2 || !array_is_list($pair)) {
                throw new SparQlException('BlankNodePropertyList expects a list of [predicate, object] pairs');
            }
            [$predicate, $object] = $pair;
            if (!($predicate instanceof TermInterface) || !($object instanceof TermInterface)) {
                throw new SparQlException('BlankNodePropertyList predicate and object must both be terms');
            }
            if ($predicate instanceof AbstractLiteral || $predicate instanceof BlankNode || $predicate instanceof TriplesNodeInterface) {
                throw new SparQlException(sprintf('BlankNodePropertyList predicate "%s" must be an IRI, path, or variable', $predicate->serialize()));
            }
            if ($object instanceof AbstractPath || $object instanceof NegatedPropertySet) {
                throw new SparQlException(sprintf('BlankNodePropertyList object "%s" must be a variable, graph term, or triples node', $object->serialize()));
            }
        }
        $this->pairs = array_values($pairs);
    }

    public function serialize(): string
    {
        $serializedPairs = array_map(function (array $pair) {
            [$predicate, $object] = $pair;
            $predicateString = $predicate instanceof RdfType ? 'a' : $predicate->serialize();
            return sprintf('%s %s', $predicateString, $object->serialize());
        }, $this->pairs);
        return sprintf('[ %s ]', implode(' ; ', $serializedPairs));
    }

    /**
     * Getters.
     */

    public function getRawValue(): string
    {
        return $this->serialize();
    }

    /**
     * @return array<int, array{0: TermInterface, 1: TermInterface}>
     */
    public function getPairs(): array
    {
        return $this->pairs;
    }

    public function getTerms(): array
    {
        $terms = [];
        foreach ($this->pairs as $pair) {
            $terms[] = $pair[0];
            $terms[] = $pair[1];
        }
        return $terms;
    }

    /**
     * Setters.
     */

    /**
     * @throws SparQlException
     */
    public function setPairs(array $pairs): TermInterface
    {
        $this->applyPairs($pairs);
        return $this;
    }
}
