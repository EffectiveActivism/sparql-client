<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\AbstractTerm;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

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

    public function typeCoercedSerialize(AbstractIri $type): string
    {
        $serializedString = $this->serialize();
        // Strip existing language tags and types.
        if (preg_match('/(".+")\^\^.+/', $serializedString, $matches)) {
            return sprintf('%s^^%s', $matches[1], $type->serialize());
        }
        elseif (preg_match('/(".+")@.+/', $serializedString, $matches)) {
            return sprintf('%s^^%s', $matches[1], $type->serialize());
        }
        else {
            return sprintf('%s^^%s', $serializedString, $type->serialize());
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
