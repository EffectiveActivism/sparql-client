<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\OrderModifierInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Constraint\Operator\OperatorInterface;
use EffectiveActivism\SparQlClient\Syntax\Statement\SelectExpression\SelectExpressionInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class SelectStatement extends AbstractConditionalStatement implements SelectStatementInterface
{
    use ResultTaggingTrait;

    protected int $limit = 0;

    protected int $offset = 0;

    protected bool $distinct = false;

    protected bool $reduced = false;

    /** @var OrderModifierInterface[] */
    protected array $orderByExpressions = [];

    /** @var array */
    protected array $groupByExpressions = [];

    protected ?OperatorInterface $havingExpression = null;

    /** @var Variable[]|SelectExpressionInterface[] */
    protected array $variables;

    /**
     * @throws SparQlException
     */
    public function __construct(array $variables)
    {
        $this->setVariables($variables);
    }

    /**
     * @throws SparQlException
     */
    public function toQuery(): string
    {
        $this->validatePrefixes($this->conditions);
        $preQuery = parent::toQuery();
        return trim($preQuery . $this->buildSelectBody($this->getDatasetClausesString()));
    }

    /**
     * Serializes this statement as a SubSelect (SPARQL 1.1 grammar production
     * `SubSelect`), suitable for inlining inside `{ … }` in another query.
     * Omits the Prologue (BASE/PREFIX) and DatasetClause (FROM/FROM NAMED),
     * neither of which are permitted inside a group graph pattern.
     *
     * @throws SparQlException
     */
    public function toSubQuery(): string
    {
        $this->validatePrefixes($this->conditions);
        return $this->buildSelectBody('');
    }

    /**
     * @throws SparQlException
     */
    private function buildSelectBody(string $datasetClauses): string
    {
        $variables = '';
        foreach ($this->variables as $variable) {
            $variables .= sprintf('%s ', $variable->serialize());
        }
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf(' %s .', $condition->serialize());
        }
        if (empty($conditionsString)) {
            throw new SparQlException('Select statement is missing a \'where\' clause');
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
        $groupByString = '';
        if (!empty($this->groupByExpressions)) {
            $groupByString = ' GROUP BY';
            foreach ($this->groupByExpressions as $groupByExpression) {
                $groupByString .= sprintf(' %s', $groupByExpression->serialize());
            }
        }
        $havingString = '';
        if ($this->havingExpression !== null) {
            $havingString = sprintf(' HAVING(%s)', $this->havingExpression->serialize());
        }
        if ($this->distinct) {
            $modifier = 'DISTINCT ';
        } elseif ($this->reduced) {
            $modifier = 'REDUCED ';
        } else {
            $modifier = '';
        }
        $plainVariables = array_filter($this->variables, fn($v) => $v instanceof Variable);
        if (!empty($plainVariables)) {
            $unclausedVariables = true;
            foreach ($plainVariables as $term) {
                foreach ($this->conditions as $condition) {
                    foreach ($condition->getTerms() as $clausedTerm) {
                        if ($clausedTerm instanceof Variable && $clausedTerm->getVariableName() === $term->getVariableName()) {
                            $unclausedVariables = false;
                            break 3;
                        }
                    }
                }
            }
            if ($unclausedVariables) {
                throw new SparQlException('At least one variable must be referenced in a \'where\' clause.');
            }
        }
        return sprintf('SELECT %s%s%sWHERE {%s }%s%s%s%s%s', $modifier, $variables, $datasetClauses, $conditionsString, $groupByString, $havingString, $orderByString, $limitString, $offsetString);
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

    /**
     * @throws SparQlException
     */
    public function distinct(): SelectStatementInterface
    {
        if ($this->reduced) {
            throw new SparQlException('Cannot use DISTINCT and REDUCED together.');
        }
        $this->distinct = true;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function reduced(): SelectStatementInterface
    {
        if ($this->distinct) {
            throw new SparQlException('Cannot use REDUCED and DISTINCT together.');
        }
        $this->reduced = true;
        return $this;
    }

    /**
     * @throws SparQlException
     */
    public function groupBy(array $expressions): SelectStatementInterface
    {
        foreach ($expressions as $expression) {
            if (!is_object($expression) || (!($expression instanceof Variable) && !($expression instanceof OperatorInterface))) {
                $class = is_object($expression) ? get_class($expression) : gettype($expression);
                throw new SparQlException(sprintf('Invalid group by expression class: %s', $class));
            }
        }
        $this->groupByExpressions = $expressions;
        return $this;
    }

    public function having(OperatorInterface $expression): SelectStatementInterface
    {
        $this->havingExpression = $expression;
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
            if (!is_object($variable) || (!($variable instanceof Variable) && !($variable instanceof SelectExpressionInterface))) {
                $class = is_object($variable) ? get_class($variable) : gettype($variable);
                throw new SparQlException(sprintf('Invalid variable class: %s', $class));
            }
        }
        $this->variables = $variables;
        return $this;
    }
}
