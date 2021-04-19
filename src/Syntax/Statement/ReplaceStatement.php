<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use InvalidArgumentException;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    protected TripleInterface $original;

    protected TripleInterface $replacement;

    public function __construct(TripleInterface $triple, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triple->getTerms() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->original = $triple;
    }

    public function with(TripleInterface $triple): ReplaceStatementInterface
    {
        foreach ($triple->getTerms() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->replacement = $triple;
        return $this;
    }

    public function toQuery(): string
    {
        if (!isset($this->replacement)) {
            throw new InvalidArgumentException('Replace (DELETE+INSERT) statement is missing a \'with\' clause');
        }
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        // At least one variable (if any) must be referenced in a 'where' clause.
        $unclausedVariables = true;
        $hasVariables = false;
        foreach (array_merge($this->original->getTerms(), $this->replacement->getTerms()) as $term) {
            if (get_class($term) === Variable::class) {
                $hasVariables = true;
                foreach ($this->conditions as $condition) {
                    foreach ($condition->getTerms() as $clausedTerm) {
                        if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                            $unclausedVariables = false;
                            break 3;
                        }
                    }
                }
            }
        }
        if ($hasVariables && $unclausedVariables) {
            throw new InvalidArgumentException('At least one variable must be referenced in a \'where\' clause.');
        }
        if (!empty($conditionsString)) {
            return sprintf('%sDELETE { %s } INSERT { %s } WHERE { %s }', $preQuery, $this->original->serialize(), $this->replacement->serialize(), $conditionsString);
        }
        else {
            // Replace statements must have a 'where' clause.
            throw new InvalidArgumentException('Replace (DELETE+INSERT) statement is missing a \'where\' clause');
        }
    }

    public function getOriginal(): TripleInterface
    {
        return $this->original;
    }

    public function getReplacement(): TripleInterface
    {
        return $this->replacement;
    }
}
