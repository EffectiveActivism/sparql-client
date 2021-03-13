<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term;

use EffectiveActivism\SparQlClient\Constant;
use InvalidArgumentException;

class PlainLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-language-identifier.
     */
    protected string|null $languageTag;

    public function __construct(bool|float|int|string $value, string $optionalLanguageTag = null)
    {
        parent::__construct($value);
        if ($optionalLanguageTag !== null && preg_match(sprintf('/%s/', Constant::LANGUAGE_TAG), $optionalLanguageTag) <= 0) {
            throw new InvalidArgumentException(sprintf('Language tag "%s" is not valid', $optionalLanguageTag));
        }
        $this->languageTag = $optionalLanguageTag;
    }

    public function serialize(): string
    {
        $wrapper = $this->serializeLiteralWrapper();
        return match (gettype($this->value)) {
            'string' => sprintf('%s%s%s%s', $wrapper, $this->value, $wrapper, empty($this->languageTag) ? '' : sprintf('@%s', $this->languageTag)),
            'integer' => sprintf('"%s"^^xsd:integer', $this->value),
            'double' => sprintf('"%s"^^xsd:decimal', $this->value),
            'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
            default => null,
        };
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function serializeLiteralWrapper(): string
    {
        if (!str_contains($this->value, '"')) {
            return '"';
        }
        elseif (!str_contains($this->value, '\'')) {
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
}
