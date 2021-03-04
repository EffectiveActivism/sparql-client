<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Primitive\Triple;

use EffectiveActivism\SparQlClient\Primitive\Term\TypeInterface;

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
        return sprintf('%s %s %s', $this->subject, $this->predicate, $this->object);
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
