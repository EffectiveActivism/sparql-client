<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

class PlainLiteral implements TypeInterface
{
    /**
     * @see https://www.w3.org/TR/sparql11-query/#QSynLiterals.
     */
    protected bool|float|int|string $value;

    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-language-identifier
     */
    protected string|null $languageTag;

    public function __construct(bool|float|int|string $value, string $optionalLanguageTag = null)
    {
        if(!match (gettype($value)) {
            'string' => preg_match(sprintf('/%s/u', Constant::LITERAL), $value) > 0,
            default => true,
        }) {
            throw new InvalidArgumentException(sprintf('Value "%s" is not a valid literal', $value));
        }
        if ($optionalLanguageTag !== null && preg_match('/%s/', Constant::LANGUAGE_TAG)) {
            throw new InvalidArgumentException(sprintf('Language tag "%s" is not valid', $optionalLanguageTag));
        }
        $this->value = $value;
        $this->languageTag = $optionalLanguageTag;
    }

    public function validate(): bool
    {
        return match (gettype($this->value)) {
            'string' => preg_match(sprintf('/%s/u', Constant::LITERAL), $this->value) > 0,
            default => true,
        };
    }

    public function serialize(): string
    {
        return match (gettype($this->value)) {
            'string' => sprintf('"""%s"""', $this->value),
            'integer' => sprintf('"%s"^^xsd:integer', $this->value),
            'double' => sprintf('"%s"^^xsd:decimal', $this->value),
            'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
            default => null,
        };
    }
}
