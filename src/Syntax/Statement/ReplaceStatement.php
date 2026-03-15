<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    /** @var PatternInterface[] */
    protected array $originals;

    /** @var PatternInterface[] */
    protected array $replacements;

    protected ?AbstractIri $scopeGraph = null;

    /**
     * @throws SparQlException
     */
    public function __construct(array $triples)
    {
        foreach ($triples as $triple) {
            if (!is_object($triple) || !($triple instanceof PatternInterface)) {
                $class = is_object($triple) ? get_class($triple) : gettype($triple);
                throw new SparQlException(sprintf('Invalid pattern class: %s', $class));
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
            if (!is_object($triple) || !($triple instanceof PatternInterface)) {
                $class = is_object($triple) ? get_class($triple) : gettype($triple);
                throw new SparQlException(sprintf('Invalid pattern class: %s', $class));
            }
        }
        $this->replacements = $triples;
        return $this;
    }

    public function usingGraph(AbstractIri $graph): static
    {
        $this->scopeGraph = $graph;
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
        $this->validatePrefixes(array_merge($this->originals, $this->replacements, $this->conditions));
        $preQuery = parent::toQuery();
        if ($this->scopeGraph !== null) {
            $preQuery .= sprintf('WITH %s ', $this->scopeGraph->serialize());
        }
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        // At least one variable (if any) must be referenced in a 'where' clause.
        $unclausedVariables = true;
        $hasVariables = false;
        foreach (array_merge($this->originals, $this->replacements) as $pattern) {
            foreach ($pattern->getTerms() as $term) {
                if ($term instanceof Variable) {
                    $hasVariables = true;
                    foreach ($this->conditions as $condition) {
                        foreach ($condition->getTerms() as $clausedTerm) {
                            if ($clausedTerm instanceof Variable && $clausedTerm->getVariableName() === $term->getVariableName()) {
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
        $originalTriplesString = implode(' . ', array_map(fn (PatternInterface $pattern) => $pattern->serialize(), $this->originals));
        $replacementTriplesString = implode(' . ', array_map(fn (PatternInterface $pattern) => $pattern->serialize(), $this->replacements));
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

    public function getScopeGraph(): ?AbstractIri
    {
        return $this->scopeGraph;
    }
}
