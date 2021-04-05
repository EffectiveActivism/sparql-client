<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use InvalidArgumentException;

class InsertStatement extends AbstractConditionalStatement implements InsertStatementInterface
{
    protected TripleInterface $tripleToInsert;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(TripleInterface $triple, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->tripleToInsert = $triple;
    }

    /**
     * @throws InvalidArgumentException
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
            foreach ($this->tripleToInsert->toArray() as $term) {
                if (get_class($term) === Variable::class) {
                    $hasVariables = true;
                    foreach ($this->conditions as $condition) {
                        if ($condition instanceof TripleInterface) {
                            foreach ($condition->toArray() as $clausedTerm) {
                                if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                                    $unclausedVariables = false;
                                }
                            }
                        }
                    }
                }
            }
            if ($hasVariables && $unclausedVariables) {
                throw new InvalidArgumentException('At least one variable must be referenced in a \'where\' clause.');
            }
            return sprintf('%sINSERT { %s } WHERE { %s }', $preQuery, $this->tripleToInsert->serialize(), $conditionsString);
        }
        else {
            // Variables are not allowed when not using 'where' clauses.
            foreach ($this->tripleToInsert->toArray() as $term) {
                if (get_class($term) === Variable::class) {
                    throw new InvalidArgumentException(sprintf('Variable "%s" cannot be inserted without being referenced in a \'where\' clause', $term->getVariableName()));
                }
            }
            return sprintf('%sINSERT DATA { %s }', $preQuery, $this->tripleToInsert->serialize());
        }
    }
}
