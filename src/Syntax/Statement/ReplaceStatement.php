<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class ReplaceStatement extends AbstractConditionalStatement implements ReplaceStatementInterface
{
    protected TripleInterface $original;

    protected TripleInterface $replacement;

    public function __construct(TripleInterface $triple, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->original = $triple;
    }

    public function with(TripleInterface $triple): ReplaceStatementInterface
    {
        foreach ($triple->toArray() as $term) {
            if (get_class($term) === PrefixedIri::class && !in_array($term->getPrefix(), array_keys($this->namespaces))) {
                throw new InvalidArgumentException(sprintf('Prefix "%s" is not defined', $term->getPrefix()));
            }
        }
        $this->replacement = $triple;
        return $this;
    }

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
        // At least one variable (if any) must be referenced in a 'where' clause.
        $unclausedVariables = true;
        $hasVariables = false;
        foreach (array_merge($this->original->toArray(), $this->replacement->toArray()) as $term) {
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
        if (!empty($conditionsString) || !empty($optionalConditionsString)) {
            return sprintf('%s DELETE { %s } INSERT { %s } WHERE { %s %s}', $preQuery, (string) $this->original, (string) $this->replacement, $conditionsString, $optionalConditionsString);
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
