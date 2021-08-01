<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    /** @var TripleInterface[] */
    protected array $originals;

    /** @var TripleInterface[] */
    protected array $replacements;

    /**
     * @throws SparQlException
     */
    public function __construct(array $triples, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triples as $triple) {
            if (is_object($triple) && $triple instanceof TripleInterface) {
                foreach ($triple->getTerms() as $term) {
                    if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                        throw new SparQlException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                    }
                }
            }
            else {
                $class = is_object($triple) ? get_class($triple) : gettype($triple);
                throw new SparQlException(sprintf('Invalid triple class: %s', $class));
            }
        }
        $this->originals = $triples;
    }

    /**
     * @throws SparQlException
     */
    public function with(array $triples): ReplaceStatementInterface
    {
        foreach ($triples as $triple) {
            if (is_object($triple) && $triple instanceof TripleInterface) {
                foreach ($triple->getTerms() as $term) {
                    if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                        throw new SparQlException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                    }
                }
            }
            else {
                $class = is_object($triple) ? get_class($triple) : gettype($triple);
                throw new SparQlException(sprintf('Invalid triple class: %s', $class));
            }
        }
        $this->replacements = $triples;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function toQuery(): string
    {
        if (!isset($this->replacements) || empty($this->replacements)) {
            throw new SparQlException('Replace (DELETE+INSERT) statement is missing a \'with\' clause');
        }
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        // At least one variable (if any) must be referenced in a 'where' clause.
        $unclausedVariables = true;
        $hasVariables = false;
        foreach (array_merge($this->originals, $this->replacements) as $triple) {
            foreach ($triple->getTerms() as $term) {
                if (get_class($term) === Variable::class) {
                    $hasVariables = true;
                    foreach ($this->conditions as $condition) {
                        foreach ($condition->getTerms() as $clausedTerm) {
                            if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                                $unclausedVariables = false;
                                break 4;
                            }
                        }
                    }
                }
            }
        }
        if ($hasVariables && $unclausedVariables) {
            throw new SparQlException('At least one variable must be referenced in a \'where\' clause.');
        }
        $originalTriplesString = implode(' . ', array_map(function (TripleInterface $triple) {
            return $triple->serialize();
        }, $this->originals));
        $replacementTriplesString = implode(' . ', array_map(function (TripleInterface $triple) {
            return $triple->serialize();
        }, $this->replacements));
        if (!empty($conditionsString)) {
            return sprintf('%sDELETE { %s } INSERT { %s } WHERE { %s }', $preQuery, $originalTriplesString, $replacementTriplesString, $conditionsString);
        }
        else {
            // Replace statements must have a 'where' clause.
            throw new SparQlException('Replace (DELETE+INSERT) statement is missing a \'where\' clause');
        }
    }

    public function getOriginals(): array
    {
        return $this->originals;
    }

    public function getReplacements(): array
    {
        return $this->replacements;
    }
}
