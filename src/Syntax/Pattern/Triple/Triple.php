<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Triple;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\RdfType;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Triple implements TripleInterface
{
    protected TermInterface $subject;

    protected TermInterface $predicate;

    protected TermInterface $object;

    /**
     * @throws SparQlException
     */
    public function __construct(TermInterface $subject, TermInterface $predicate, TermInterface $object)
    {
        if ($subject instanceof AbstractLiteral) {
            throw new SparQlException('Triple subject cannot be a literal');
        }
        if ($predicate instanceof AbstractLiteral || $predicate instanceof BlankNode) {
            throw new SparQlException('Triple predicate must be an IRI, path, or variable');
        }
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
    }

    public function serialize(): string
    {
        $predicateStr = $this->predicate instanceof RdfType ? 'a' : $this->predicate->serialize();
        return sprintf('%s %s %s', $this->subject->serialize(), $predicateStr, $this->object->serialize());
    }

    public function toArray(): array
    {
        return $this->getTerms();
    }

    /**
     * Getters.
     */

    public function getObject(): TermInterface
    {
        return $this->object;
    }

    public function getPredicate(): TermInterface
    {
        return $this->predicate;
    }

    public function getSubject(): TermInterface
    {
        return $this->subject;
    }

    public function getTerms(): array
    {
        return [$this->subject, $this->predicate, $this->object];
    }

    /**
     * Setters.
     */

    public function setObject(TermInterface $term): TripleInterface
    {
        $this->object = $term;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function setPredicate(TermInterface $term): TripleInterface
    {
        if ($term instanceof AbstractLiteral || $term instanceof BlankNode) {
            throw new SparQlException('Triple predicate must be an IRI, path, or variable');
        }
        $this->predicate = $term;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function setSubject(TermInterface $term): TripleInterface
    {
        if ($term instanceof AbstractLiteral) {
            throw new SparQlException('Triple subject cannot be a literal');
        }
        $this->subject = $term;
        return $this;
    }
}
