<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

class InsertStatement extends AbstractConditionalStatement implements InsertStatementInterface
{
    /** @var TripleInterface[] */
    protected array $triplesToInsert;

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
        $this->triplesToInsert = $triples;
    }

    /**
     * @throws SparQlException
     */
    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        if (!empty($conditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            $hasVariables = false;
            foreach ($this->triplesToInsert as $triple) {
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
            $triplesToInsertString = implode(' . ', array_map(function (TripleInterface $triple) {
                return $triple->serialize();
            }, $this->triplesToInsert));
            return sprintf('%sINSERT { %s } WHERE { %s }', $preQuery, $triplesToInsertString, $conditionsString);
        }
        else {
            // Variables are not allowed when not using 'where' clauses.
            foreach ($this->triplesToInsert as $triple) {
                foreach ($triple->getTerms() as $term) {
                    if (get_class($term) === Variable::class) {
                        throw new SparQlException(sprintf('Variable "%s" cannot be inserted without being referenced in a \'where\' clause', $term->getVariableName()));
                    }
                }
            }
            $triplesToInsertString = implode(' . ', array_map(function (TripleInterface $triple) {
                return $triple->serialize();
            }, $this->triplesToInsert));
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
