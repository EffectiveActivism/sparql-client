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
        return match (gettype($this->value)) {
            'string' => sprintf('"""%s"""', $this->value),
            'integer' => sprintf('"%s"^^xsd:integer', $this->value),
            'double' => sprintf('"%s"^^xsd:decimal', $this->value),
            'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
            default => null,
        };
    }
}
