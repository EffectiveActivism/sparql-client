<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class DeleteStatement extends AbstractConditionalStatement implements DeleteStatementInterface
{
    protected TripleInterface $tripleToDelete;

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
        $this->tripleToDelete = $triple;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $triple) {
            $conditionsString .= sprintf('%s .', $triple);
        }
        $optionalConditionsString = '';
        foreach ($this->optionalConditions as $triple) {
            $optionalConditionsString .= sprintf('OPTIONAL {%s} .', $triple);
        }
        if (!empty($conditionsString) || !empty($optionalConditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            $hasVariables = false;
            foreach ($this->tripleToDelete->toArray() as $term) {
                if (get_class($term) === Variable::class) {
                    $hasVariables = true;
                    /** @var TripleInterface $triple */
                    foreach (array_merge($this->conditions, $this->optionalConditions) as $triple) {
                        foreach ($triple->toArray() as $clausedTerm) {
                            if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                                $unclausedVariables = false;
                            }
                        }
                    }
                }
            }
            if ($hasVariables && $unclausedVariables) {
                throw new InvalidArgumentException('At least one variable must be referenced in a \'where\' clause.');
            }
            return sprintf('%s DELETE { %s } WHERE { %s %s}', $preQuery, (string) $this->tripleToDelete, $conditionsString, $optionalConditionsString);
        }
        else {
            // Variables are not allowed when not using 'where' clauses.
            foreach ($this->tripleToDelete->toArray() as $term) {
                if (get_class($term) === Variable::class) {
                    throw new InvalidArgumentException(sprintf('Variable "%s" cannot be deleted without being referenced in a \'where\' clause', $term->getVariableName()));
                }
            }
            return sprintf('%s DELETE DATA { %s }', $preQuery, (string) $this->tripleToDelete);
        }
    }

    /**
     * Getters.
     */

    public function getTripleToDelete(): TripleInterface
    {
        return $this->tripleToDelete;
    }
}
