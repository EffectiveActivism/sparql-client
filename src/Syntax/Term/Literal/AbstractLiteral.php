<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use InvalidArgumentException;

abstract class AbstractLiteral extends AbstractTerm implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/sparql11-query/#QSynLiterals.
     */
    protected bool|float|int|string $value;

    public function __construct(bool|float|int|string $value)
    {
        $this->value = $value;
    }

    abstract public function serialize(): string;

    /**
     * @throws InvalidArgumentException
     */
    protected function serializeLiteralWrapper(): string
    {
        if (!is_string($this->value)) {
            return '"';
        }
        elseif (!str_contains($this->value, '"') && !str_contains("\n", $this->value)) {
            return '"';
        }
        elseif (!str_contains($this->value, '\'') && !str_contains("\n", $this->value)) {
            return '\'';
        }
        elseif (!str_contains($this->value, '"""')) {
            return '"""';
        }
        elseif (!str_contains($this->value, '\'\'\'')) {
            return '\'\'\'';
        }
        else {
            throw new InvalidArgumentException(sprintf('Literal value "%s" cannot be parsed', $this->value));
        }
    }

    /**
     * Getters.
     */

    public function getRawValue(): string
    {
        return (string) $this->value;
    }

    abstract public function getType(): string;
}
