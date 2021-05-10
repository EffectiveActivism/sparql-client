<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Variable;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

/**
 * @see https://www.w3.org/TR/sparql11-query/#QSynVariables
 */
class Variable extends AbstractTerm implements TermInterface
{
    protected string $value;

    /**
     * @throws SparQlException
     */
    public function __construct(string $value)
    {
        if (preg_match(sprintf('/%s/u', Constant::VARNAME), $value) <= 0) {
            throw new SparQlException(sprintf('Value "%s" is not a valid variable name', $value));
        }
        $this->value = $value;
        $this->variableName = $value;
    }

    public function serialize(): string
    {
        return sprintf('?%s', $this->value);
    }

    public function getRawValue(): string
    {
        return $this->value;
    }
}
