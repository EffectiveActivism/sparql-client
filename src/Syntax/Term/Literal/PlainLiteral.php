<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Syntax\Term\Literal;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;

class PlainLiteral extends AbstractLiteral implements TermInterface
{
    /**
     * @see https://www.w3.org/TR/2004/REC-rdf-concepts-20040210/#dfn-language-identifier.
     */
    protected string|null $languageTag;

    /**
     * @throws SparQlException
     */
    public function __construct(bool|float|int|string $value, string $optionalLanguageTag = null)
    {
        parent::__construct($value);
        if ($optionalLanguageTag !== null && preg_match(sprintf('/%s/', Constant::LANGUAGE_TAG), $optionalLanguageTag) <= 0) {
            throw new SparQlException(sprintf('Language tag "%s" is not valid', $optionalLanguageTag));
        }
        $this->languageTag = $optionalLanguageTag;
    }

    /**
     * @throws SparQlException
     */
    public function serialize(): string
    {
        return match (gettype($this->value)) {
            'boolean' => sprintf('"%s"^^xsd:boolean', $this->value ? 'true' : 'false'),
            'double' => sprintf('"%s"^^xsd:decimal', $this->value),
            'integer' => sprintf('"%s"^^xsd:integer', $this->value),
            'string' => sprintf(
                '"""%s"""%s',
                str_replace(
                    ['\\', '"', '\''],
                    ['\\\\', '\"', '\\\''],
                    $this->value
                ),
                empty($this->languageTag) ? '' : sprintf('@%s', $this->languageTag)
            ),
            default => null,
        };
    }

    /**
     * Getters.
     */

    /**
     * @throws SparQlException
     */
    public function getType(): string
    {
        return match (gettype($this->value)) {
            'boolean' => 'xsd:boolean',
            'double' => 'xsd:decimal',
            'integer' => 'xsd:integer',
            'string' => 'xsd:string',
            default => throw new SparQlException(sprintf('Plain literal "%s" has unknown type "%s"', $this->getRawValue(), gettype($this->value))),
        };
    }
}
