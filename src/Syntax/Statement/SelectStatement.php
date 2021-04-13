<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;
use InvalidArgumentException;

class SelectStatement extends AbstractConditionalStatement implements SelectStatementInterface
{
    protected int $limit = 0;

    protected int $offset = 0;

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
            $conditionsString .= sprintf(' %s .', $condition->serialize());
        }
        $limitString = '';
        if ($this->limit > 0) {
            $limitString = sprintf('LIMIT %d', $this->limit);
        }
        $offsetString = '';
        if ($this->offset > 0) {
            $offsetString = sprintf('OFFSET %d', $this->offset);
        }
        if (!empty($conditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            foreach ($this->variables as $term) {
                foreach ($this->conditions as $condition) {
                    foreach ($condition->toArray() as $clausedTerm) {
                        if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                            $unclausedVariables = false;
                            break 3;
                        }
                    }
                }
            }
            if ($unclausedVariables) {
                throw new InvalidArgumentException('At least one variable must be referenced in a \'where\' clause.');
            }
            return trim(sprintf('%sSELECT %sWHERE {%s } %s %s', $preQuery, $variables, $conditionsString, $limitString, $offsetString));
        }
        else {
            // Select statements must have a 'where' clause.
            throw new InvalidArgumentException('Select statement is missing a \'where\' clause');
        }
    }

    public function limit(int $limit): SelectStatementInterface
    {
        if ($limit < 0) {
            throw new InvalidArgumentException(sprintf('Limit must be non-negative, "%d" provided', $limit));
        }
        $this->limit = $limit;
        return $this;
    }

    public function offset(int $offset): SelectStatementInterface
    {
        if ($offset < 0) {
            throw new InvalidArgumentException(sprintf('Offset must be non-negative, "%d" provided', $offset));
        }
        $this->offset = $offset;
        return $this;
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
