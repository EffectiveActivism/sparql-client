<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\OrderModifierInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class SelectStatement extends AbstractConditionalStatement implements SelectStatementInterface
{
    protected int $limit = 0;

    protected int $offset = 0;

    /** @var OrderModifierInterface[] */
    protected array $orderByExpressions = [];

    /** @var Variable[] */
    protected array $variables;

    /**
     * @throws SparQlException
     */
    public function __construct(array $variables, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        $this->setVariables($variables);
    }

    /**
     * @throws SparQlException
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
            $limitString = sprintf(' LIMIT %d', $this->limit);
        }
        $offsetString = '';
        if ($this->offset > 0) {
            $offsetString = sprintf(' OFFSET %d', $this->offset);
        }
        $orderByString = empty($this->orderByExpressions) ? '' : ' ORDER BY';
        foreach ($this->orderByExpressions as $orderByExpression) {
            $orderByString .= sprintf(' %s', $orderByExpression->serialize());
        }
        if (!empty($conditionsString)) {
            // At least one variable (if any) must be referenced in a 'where' clause.
            $unclausedVariables = true;
            foreach ($this->variables as $term) {
                foreach ($this->conditions as $condition) {
                    foreach ($condition->getTerms() as $clausedTerm) {
                        if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                            $unclausedVariables = false;
                            break 3;
                        }
                    }
                }
            }
            if ($unclausedVariables) {
                throw new SparQlException('At least one variable must be referenced in a \'where\' clause.');
            }
            return trim(sprintf('%sSELECT %sWHERE {%s }%s%s%s', $preQuery, $variables, $conditionsString, $orderByString, $limitString, $offsetString));
        }
        else {
            // Select statements must have a 'where' clause.
            throw new SparQlException('Select statement is missing a \'where\' clause');
        }
    }

    /**
     * @throws SparQlException
     */
    public function limit(int $limit): SelectStatementInterface
    {
        if ($limit < 0) {
            throw new SparQlException(sprintf('Limit must be non-negative, "%d" provided', $limit));
        }
        $this->limit = $limit;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function offset(int $offset): SelectStatementInterface
    {
        if ($offset < 0) {
            throw new SparQlException(sprintf('Offset must be non-negative, "%d" provided', $offset));
        }
        $this->offset = $offset;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function orderBy(array $expressions): SelectStatementInterface
    {
        foreach ($expressions as $expression) {
            if (!is_object($expression) || !($expression instanceof OrderModifierInterface)) {
                $class = is_object($expression) ? get_class($expression) : gettype($expression);
                throw new SparQlException(sprintf('Invalid order modifier class: %s', $class));
            }
        }
        $this->orderByExpressions = $expressions;
        return $this;
    }

    public function getVariables(): array
    {
        return $this->variables;
    }

    /**
     * @throws SparQlException
     */
    public function setVariables(array $variables): SelectStatementInterface
    {
        foreach ($variables as $variable) {
            if (get_class($variable) !== Variable::class) {
                throw new SparQlException(sprintf('Invalid variable class: %s', get_class($variable)));
            }
        }
        $this->variables = $variables;
        return $this;
    }
}
