<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

/**
 * @see https://www.w3.org/TR/sparql11-query/#QSynVariables
 */
class Variable implements TypeInterface
{
    protected string $value;

    public function __construct(string $value)
    {
        if (preg_match(sprintf('/%s/u', Constant::VARNAME), $value) <= 0) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid variable name', $value));
        }
        $this->value = $value;
    }

    public function serialize(): string
    {
        return sprintf('?%s', $this->value);
    }
}
