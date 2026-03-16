<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Result;

use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

interface SelectResultInterface extends StatementResultInterface
{
    /**
     * Returns variable bindings as an array of associative arrays keyed by variable name.
     *
     * @return array<int, array<string, TermInterface>>
     */
    public function getRows(): array;
}
