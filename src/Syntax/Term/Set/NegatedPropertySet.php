<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Set;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Path\AbstractPath;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class NegatedPropertySet extends AbstractTerm implements TermInterface
{
    /** @var TermInterface[] */
    protected array $terms;

    /**
     * @throws SparQlException
     */
    public function __construct(array $terms)
    {
        /** @var TermInterface $term */
        foreach ($terms as $term) {
            if (!($term instanceof AbstractIri) && !($term instanceof AbstractPath)) {
                throw new SparQlException(sprintf('Term "%s" is not a valid IRI or path.', $term->serialize()));
            }
        }
        $this->terms = array_values($terms);
    }

    public function serialize(): string
    {
        $serializedValue = implode(' | ', array_map(function (TermInterface $term) {
            return $term instanceof AbstractPath ? sprintf('(%s)', $term->serialize()) : $term->serialize();
        }, $this->terms));
        return count($this->terms) > 1 ? sprintf('!(%s)', $serializedValue) : sprintf('!%s', $serializedValue);
    }

    /**
     * Getters.
     */

    public function getRawValue(): string
    {
        if (!empty($this->terms)) {
            return $this->terms[0]->getRawValue();
        }
        return '';
    }

    public function getTerms(): array
    {
        return $this->terms;
    }

    public function getVariableName(): string
    {
        if (!empty($this->terms)) {
            return $this->terms[0]->getVariableName();
        }
        return '';
    }

    /**
     * Setters.
     */

    public function setTerms(array $terms): TermInterface
    {
        $this->terms = $terms;
        return $this;
    }

    public function setVariableName(string $variableName): TermInterface
    {
        if (!empty($this->terms)) {
            $this->terms[0]->setVariableName($variableName);
        }
        return $this;
    }
}
