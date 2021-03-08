<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

abstract class AbstractLiteral extends AbstractTerm implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/sparql11-query/#QSynLiterals.
     */
    protected bool|float|int|string $value;

    public function __construct(bool|float|int|string $value)
    {
        if(!match (gettype($value)) {
            'string' => preg_match(sprintf('/%s/u', Constant::LITERAL), $value) > 0,
            default => true,
        }) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid literal', $value));
        }
        $this->value = $value;
    }

    abstract public function serialize(): string;
}
