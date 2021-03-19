<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractUnaryPath extends AbstractPath implements TermInterface
{
    protected AbstractIri|AbstractPath $term;

    public function __construct(AbstractIri|AbstractPath $term)
    {
        $this->term = $term;
    }

    /**
     * Getters.
     */

    public function getRawValue(): string
    {
        return $this->term->getRawValue();
    }

    public function getTerm(): AbstractIri|AbstractPath
    {
        return $this->term;
    }

    public function getVariableName(): string
    {
        return $this->term->getVariableName();
    }

    /**
     * Setters.
     */

    public function setTerm(AbstractIri|AbstractPath $term): TermInterface
    {
        $this->term = $term;
        return $this;
    }

    public function setVariableName(string $variableName): TermInterface
    {
        $this->term->setVariableName($variableName);
        return $this;
    }
}
