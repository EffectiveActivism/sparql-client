<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

use EffectiveActivism\SparQlClient\Syntax\Term\Variable;
use InvalidArgumentException;

class SelectStatement extends AbstractConditionalStatement implements SelectStatementInterface
{
    /** @var Variable[] */
    protected array $variables;

    public function __construct(array $variables, array $extraNamespaces = [])
    {
        parent::__construct($extraNamespaces);
        foreach ($variables as $variable) {
            if (get_class($variable) !== Variable::class) {
                throw new InvalidArgumentException(sprintf('Invalid variable class: %s', get_class($variable)));
            }
        }
        $this->variables = $variables;
    }

    public function toQuery(): string
    {
        $query = parent::toQuery();
        $variables = '';
        foreach ($this->variables as $variable) {
            $variables .= sprintf('%s ', $variable->serialize());
        }
        $conditions = '';
        foreach ($this->conditions as $triple) {
            $conditions .= sprintf('%s .', $triple);
        }
        $optionalConditions = '';
        foreach ($this->optionalConditions as $triple) {
            $optionalConditions .= sprintf('OPTIONAL {%s} .', $triple);
        }
        if (!empty($conditions) || !empty($optionalConditions)) {
            return sprintf('%s SELECT { %s} WHERE { %s %s}', $query, $variables, $conditions, $optionalConditions);
        }
        else {
            return sprintf('%s SELECT { %s}', $query, $variables);
        }
    }
}
