<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class InsertStatement extends AbstractConditionalStatement implements InsertStatementInterface
{
    /** @var PatternInterface[] */
    protected array $triplesToInsert;

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
        $this->triplesToInsert = $triples;
    }

    /**
     * @throws SparQlException
     */
    public function toQuery(): string
    {
        $this->validatePrefixes(array_merge($this->triplesToInsert, $this->conditions));
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        if (!empty($conditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            $hasVariables = false;
            foreach ($this->triplesToInsert as $pattern) {
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
            $triplesToInsertString = implode(' . ', array_map(fn (PatternInterface $pattern) => $pattern->serialize(), $this->triplesToInsert));
            return sprintf('%sINSERT { %s } WHERE { %s }', $preQuery, $triplesToInsertString, $conditionsString);
        }
        else {
            // Variables are not allowed when not using 'where' clauses.
            foreach ($this->triplesToInsert as $pattern) {
                foreach ($pattern->getTerms() as $term) {
                    if ($term instanceof Variable) {
                        throw new SparQlException(sprintf('Variable "%s" cannot be inserted without being referenced in a \'where\' clause', $term->getVariableName()));
                    }
                }
            }
            $triplesToInsertString = implode(' . ', array_map(fn (PatternInterface $pattern) => $pattern->serialize(), $this->triplesToInsert));
            return sprintf('%sINSERT DATA { %s }', $preQuery, $triplesToInsertString);
        }
    }

    /**
     * Getters.
     */

    public function getTriplesToInsert(): array
    {
        return $this->triplesToInsert;
    }
}
