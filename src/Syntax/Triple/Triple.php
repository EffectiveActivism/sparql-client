<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Triple;

use EffectiveActivism\SparQlClient\Syntax\Term\TypeInterface;

class Triple implements TripleInterface
{
    protected TypeInterface $subject;

    protected TypeInterface $predicate;

    protected TypeInterface $object;

    public function __construct(TypeInterface $subject, TypeInterface $predicate, TypeInterface $object)
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

    public function getObject(): TypeInterface
    {
        return $this->object;
    }

    public function getPredicate(): TypeInterface
    {
        return $this->predicate;
    }

    public function getSubject(): TypeInterface
    {
        return $this->subject;
    }
}
