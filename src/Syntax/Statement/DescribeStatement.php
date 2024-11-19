<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Order\OrderModifierInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Variable\Variable;

class DescribeStatement extends AbstractConditionalStatement implements DescribeStatementInterface
{
    protected int $limit = 0;

    protected int $offset = 0;

    /** @var OrderModifierInterface[] */
    protected array $orderByExpressions = [];

    /** @var (AbstractIri|Variable)[] */
    protected array $resources;

    public function __construct(array $resources, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($resources as $resource) {
            if (!($resource instanceof AbstractIri) && !($resource instanceof Variable)) {
                $class = is_object($resource) ? get_class($resource) : gettype($resource);
                throw new SparQlException(sprintf('Invalid triple class: %s', $class));
            }
        }
        $this->resources = $resources;
    }

    /**
     * @throws SparQlException
     */
    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $resources = '';
        foreach ($this->resources as $resource) {
            $resources .= sprintf('%s ', $resource->serialize());
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
            $hasVariables = false;
            $unclausedVariables = true;
            foreach ($this->resources as $term) {
                if ($term instanceof Variable) {
                    $hasVariables = true;
                    foreach ($this->conditions as $condition) {
                        foreach ($condition->getTerms() as $clausedTerm) {
                            if (get_class($clausedTerm) === Variable::class && $clausedTerm->getVariableName() === $term->getVariableName()) {
                                $unclausedVariables = false;
                                break 3;
                            }
                        }
                    }
                }
            }
            if ($unclausedVariables && $hasVariables) {
                throw new SparQlException('At least one variable must be referenced in a \'where\' clause.');
            }
            return trim(sprintf('%sDESCRIBE %sWHERE {%s }%s%s%s', $preQuery, $resources, $conditionsString, $orderByString, $limitString, $offsetString));
        }
        else {
            return trim(sprintf('%sDESCRIBE %s%s%s%s', $preQuery, $resources, $orderByString, $limitString, $offsetString));
        }
    }

    /**
     * @throws SparQlException
     */
    public function limit(int $limit): DescribeStatementInterface
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
    public function offset(int $offset): DescribeStatementInterface
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
    public function orderBy(array $expressions): DescribeStatementInterface
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

    public function getResources(): array
    {
        return $this->resources;
    }
}