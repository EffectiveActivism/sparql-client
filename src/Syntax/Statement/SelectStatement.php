<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Constraint\ConstraintInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use EffectiveActivism\SparQlClient\Syntax\Triple\TripleInterface;
use InvalidArgumentException;

class SelectStatement extends AbstractConditionalStatement implements SelectStatementInterface
{
    /** @var Variable[] */
    protected array $variables;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(array $variables, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        $this->setVariables($variables);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $variables = '';
        foreach ($this->variables as $variable) {
            $variables .= sprintf('%s ', $variable->serialize());
        }
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf(' %s .', $condition);
        }
        $optionalConditionsString = '';
        foreach ($this->optionalConditions as $condition) {
            $optionalConditionsString .= sprintf('OPTIONAL {%s} .', $condition);
        }
        if (!empty($conditionsString) || !empty($optionalConditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            foreach ($this->variables as $term) {
                /** @var TripleInterface|ConstraintInterface $triple */
                foreach (array_merge($this->conditions, $this->optionalConditions) as $condition) {
                    if ($condition instanceof TripleInterface) {
                        foreach ($condition->toArray() as $clausedTerm) {
                            if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                                $unclausedVariables = false;
                            }
                        }
                    }
                }
            }
            if ($unclausedVariables) {
                throw new InvalidArgumentException('At least one variable must be referenced in a \'where\' clause.');
            }
            return sprintf('%sSELECT %sWHERE {%s %s}', $preQuery, $variables, $conditionsString, $optionalConditionsString);
        }
        else {
            // Select statements must have a 'where' clause.
            throw new InvalidArgumentException('Select statement is missing a \'where\' clause');
        }
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    public function setVariables(array $variables): SelectStatementInterface
    {
        foreach ($variables as $variable) {
            if (get_class($variable) !== Variable::class) {
                throw new InvalidArgumentException(sprintf('Invalid variable class: %s', get_class($variable)));
            }
        }
        $this->variables = $variables;
        return $this;
    }
}
