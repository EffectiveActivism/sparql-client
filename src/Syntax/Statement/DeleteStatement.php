<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

class DeleteStatement extends AbstractConditionalStatement implements DeleteStatementInterface
{
    /** @var TripleInterface[] */
    protected array $triplesToDelete;

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
        $this->triplesToDelete = $triples;
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
            foreach ($this->triplesToDelete as $triple) {
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
            $triplesToDeleteString = implode(' . ', array_map(function (TripleInterface $triple) {
                return $triple->serialize();
            }, $this->triplesToDelete));
            return sprintf('%sDELETE { %s } WHERE { %s }', $preQuery, $triplesToDeleteString, $conditionsString);
        }
        else {
            // Variables are not allowed when not using 'where' clauses.
            foreach ($this->triplesToDelete as $triple) {
                foreach ($triple->getTerms() as $term) {
                    if (get_class($term) === Variable::class) {
                        throw new SparQlException(sprintf('Variable "%s" cannot be deleted without being referenced in a \'where\' clause', $term->getVariableName()));
                    }
                }
            }
            $triplesToDeleteString = implode(' . ', array_map(function (TripleInterface $triple) {
                return $triple->serialize();
            }, $this->triplesToDelete));
            return sprintf('%sDELETE DATA { %s }', $preQuery, $triplesToDeleteString);
        }
    }

    /**
     * Getters.
     */

    public function getTriplesToDelete(): array
    {
        return $this->triplesToDelete;
    }
}
