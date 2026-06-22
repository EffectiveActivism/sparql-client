<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\TriplesNode;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AbstractPath;
use EffectiveActivism\SparQlClient\Syntax\Term\Set\NegatedPropertySet;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

/**
 * An RDF Collection, serialised as `( m1 m2 … )`.
 *
 * Each member is a GraphNode (a variable, a graph term such as an IRI,
 * literal or blank node, or a nested TriplesNode). Property paths and
 * negated property sets are predicate-only constructs and are therefore not
 * valid members.
 *
 * @see https://www.w3.org/TR/sparql11-query/#rCollection
 * @see https://www.w3.org/TR/sparql11-query/#collections
 */
class Collection extends AbstractTerm implements TriplesNodeInterface
{
    /** @var TermInterface[] */
    protected array $members;

    /**
     * @throws SparQlException
     */
    public function __construct(array $members)
    {
        $this->applyMembers($members);
    }

    /**
     * @throws SparQlException
     */
    private function applyMembers(array $members): void
    {
        if (empty($members)) {
            throw new SparQlException('Collection requires at least one member');
        }
        foreach ($members as $member) {
            if (!($member instanceof TermInterface)) {
                $class = is_object($member) ? get_class($member) : gettype($member);
                throw new SparQlException(sprintf('Collection member "%s" is not a valid term', $class));
            }
            if ($member instanceof AbstractPath || $member instanceof NegatedPropertySet) {
                throw new SparQlException(sprintf('Collection member "%s" must be a variable, graph term, or triples node', $member->serialize()));
            }
        }
        $this->members = array_values($members);
    }

    public function serialize(): string
    {
        return sprintf('( %s )', implode(' ', array_map(fn (TermInterface $member) => $member->serialize(), $this->members)));
    }

    /**
     * Getters.
     */

    public function getRawValue(): string
    {
        return $this->serialize();
    }

    public function getMembers(): array
    {
        return $this->members;
    }

    public function getTerms(): array
    {
        return $this->members;
    }

    /**
     * Setters.
     */

    /**
     * @throws SparQlException
     */
    public function setMembers(array $members): TermInterface
    {
        $this->applyMembers($members);
        return $this;
    }
}
