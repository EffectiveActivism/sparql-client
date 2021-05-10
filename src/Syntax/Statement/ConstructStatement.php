<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;

class ConstructStatement extends AbstractConditionalStatement implements ConstructStatementInterface
{
    protected array $triplesToConstruct;

    /**
     * @throws SparQlException
     */
    public function __construct(array $triples, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triples as $triple) {
            if (!($triple instanceof TripleInterface)) {
                throw new SparQlException(sprintf('Invalid triple class: %s', gettype($triple)));
            }
            foreach ($triple->getTerms() as $term) {
                if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                    throw new SparQlException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
                }
            }
        }
        $this->triplesToConstruct = $triples;
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
            foreach ($this->triplesToConstruct as $triple) {
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
            $tripleString = '';
            /** @var TripleInterface $triple */
            foreach ($this->triplesToConstruct as $triple) {
                $tripleString .= sprintf('%s . ', $triple->serialize());
            }
            return sprintf('%sCONSTRUCT { %s } WHERE { %s }', $preQuery, $tripleString, $conditionsString);
        }
        else {
            throw new SparQlException('Construct statement is missing a set of triples.');
        }
    }

    /**
     * Getters.
     */

    public function getTriplesToConstruct(): array
    {
        return $this->triplesToConstruct;
    }
}
