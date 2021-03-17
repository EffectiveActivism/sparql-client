<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Path;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

abstract class AbstractBinaryPath extends AbstractPath implements TermInterface
{
    protected AbstractIri|AbstractPath $term1;

    protected AbstractIri|AbstractPath $term2;

    public function __construct(AbstractIri|AbstractPath $term1, AbstractIri|AbstractPath $term2)
    {
        $this->term1 = $term1;
        $this->term2 = $term2;
    }

    /**
     * Getters.
     */

    public function getTerm1(): AbstractIri|AbstractPath
    {
        return $this->term1;
    }

    public function getTerm2(): AbstractIri|AbstractPath
    {
        return $this->term2;
    }

    public function getVariableName(): string
    {
        return $this->term1->getVariableName();
    }

    /**
     * Setters.
     */

    public function setTerm1(AbstractIri|AbstractPath $term1): TermInterface
    {
        $this->term1 = $term1;
        return $this;
    }

    public function setTerm2(AbstractIri|AbstractPath $term2): TermInterface
    {
        $this->term2 = $term2;
        return $this;
    }

    public function setVariableName(string $variableName): TermInterface
    {
        $this->term1->setVariableName($variableName);
        return $this;
    }
}
