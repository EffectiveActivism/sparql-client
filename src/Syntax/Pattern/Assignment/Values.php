<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Pattern\Assignment;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class Values implements ValuesInterface
{
    protected array $variables;

    protected array $values;

    /**
     * @see https://www.w3.org/TR/sparql11-query/#values.
     */
    public function __construct(array $variables, array $values)
    {
        foreach ($variables as $variable) {
            if (!is_object($variable) || (!($variable instanceof Variable))) {
                $class = is_object($variable) ? get_class($variable) : gettype($variable);
                throw new SparQlException(sprintf('Invalid assignment class: %s', $class));
            }
        }
        foreach ($values as $valueSet) {
            if (!is_array($valueSet) || count($variables) !== count($valueSet)) {
                throw new SparQlException('Dimensional mismatch: value dimensions must match number of variables. Use \'null\' for undefined values');
            }
            foreach ($valueSet as $value) {
                if (!is_null($value) && (!is_object($value) || (!($value instanceof AbstractLiteral) && !($value instanceof AbstractIri)))) {
                    $class = is_object($value) ? get_class($value) : gettype($value);
                    throw new SparQlException(sprintf('Invalid value class: %s', $class));
                }
            }
        }
        $this->variables = $variables;
        $this->values = $values;
    }

    public function toArray(): array
    {
        return $this->getTerms();
    }

    public function getTerms(): array
    {
        $terms = $this->variables;
        foreach ($this->values as $valueSet) {
            foreach ($valueSet as $value) {
                if ($value !== null) {
                    $terms[] = $value;
                }
            }
        }
        return $terms;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function serialize(): string
    {
        $variablesString = '';
        foreach ($this->variables as $pattern) {
            $variablesString .= sprintf('%s ', $pattern->serialize());
        }
        $valueSetString = '';
        foreach ($this->values as $valueSet) {
            $valueString = '';
            /** @var AbstractIri|AbstractLiteral|null $value */
            foreach ($valueSet as $value) {
                if ($value === null) {
                    $valueString .= sprintf('%s ', 'UNDEF');
                }
                else {
                    $valueString .= sprintf('%s ', $value->serialize());
                }
            }
            $valueSetString .= sprintf(' ( %s)', $valueString);
        }
        return sprintf('VALUES ( %s) {%s }', $variablesString, $valueSetString);
    }
}
