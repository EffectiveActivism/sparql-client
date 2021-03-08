<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Triple;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class Triple implements TripleInterface
{
    protected TermInterface $subject;

    protected TermInterface $predicate;

    protected TermInterface $object;

    public function __construct(TermInterface $subject, TermInterface $predicate, TermInterface $object)
    {
        $this->subject = $subject;
        $this->predicate = $predicate;
        $this->object = $object;
    }

    public function __toString(): string
    {
        return sprintf('%s %s %s', $this->subject->serialize(), $this->predicate->serialize(), $this->object->serialize());
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

    /**
     * Setters.
     */

    public function setObject(TermInterface $term): TripleInterface
    {
        $this->object = $term;
        return $this;
    }

    public function setPredicate(TermInterface $term): TripleInterface
    {
        $this->predicate = $term;
        return $this;
    }

    public function setSubject(TermInterface $term): TripleInterface
    {
        $this->subject = $term;
        return $this;
    }
}
