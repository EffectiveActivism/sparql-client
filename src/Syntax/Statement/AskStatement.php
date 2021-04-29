<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Statement;

class AskStatement extends AbstractConditionalStatement implements AskStatementInterface
{
    public function toQuery(): string
    {
        $preQuery = parent::toQuery();
        $conditionsString = '';
        foreach ($this->conditions as $condition) {
            $conditionsString .= sprintf('%s .', $condition->serialize());
        }
        return sprintf('%sASK { %s }', $preQuery, $conditionsString);
    }
}
