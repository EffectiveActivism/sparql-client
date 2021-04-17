<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\InvalidResultException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SparQlResultDenormalizer implements DenormalizerInterface
{
    const TYPE = 'sparql-result';

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @throws InvalidResultException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): array
    {
        $sets = [];
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $result) {
                if (is_array($result)) {
                    if (key($result) === 'binding') {
                        $sets[] = $this->getTerms($result[key($result)]);
                    }
                    elseif (isset($result[0]) && is_array($result[0]) && key($result[0]) === 'binding') {
                        foreach ($result as $binding) {
                            $sets[] = $this->getTerms($binding['binding']);
                        }
                    }
                }
            }
        }
        return $sets;
    }

    /**
     * @throws InvalidResultException
     */
    protected function getTerms(array $binding): array
    {
        $terms = [];
        if (isset($binding['@name'])) {
            $term = $this->getTerm($binding);
            $term->setVariableName($binding['@name']);
            $terms[$binding['@name']] = $term;
        }
        elseif (isset($binding[0]['@name'])) {
            foreach ($binding as $termData) {
                $term = $this->getTerm($termData);
                $term->setVariableName($termData['@name']);
                $terms[$termData['@name']] = $term;
            }
        }
        return $terms;
    }

    /**
     * @throws InvalidResultException
     */
    protected function getTerm(array $termData): TermInterface
    {
        if (isset($termData['literal'])) {
            // Determine literal type.
            if (is_array($termData['literal']) && isset($termData['literal']['@xml:lang'])) {
                $languageTag = $termData['literal']['@xml:lang'];
                $value = isset($termData['literal']['#']) ? $termData['literal']['#'] : '';
                return new PlainLiteral($value, $languageTag);
            } elseif (is_array($termData['literal']) && isset($termData['literal']['@datatype'])) {
                $dataType = $termData['literal']['@datatype'];
                $value = isset($termData['literal']['#']) ? $termData['literal']['#'] : '';
                if (filter_var($dataType, FILTER_VALIDATE_URL) || preg_match(sprintf('/%s/', Constant::URN), $dataType)) {
                    return new TypedLiteral($value, new Iri($dataType));
                } elseif (count(explode(':', $dataType)) === 2) {
                    list($prefix, $localPart) = explode(':', $dataType);
                    return new TypedLiteral($value, new PrefixedIri($prefix, $localPart));
                }
            } elseif (is_string($termData['literal'])) {
                return new PlainLiteral($termData['literal']);
            } else {
                throw new InvalidResultException(sprintf('Result "%s" is not a valid literal', serialize($termData['literal'])));
            }
        }
        elseif (isset($termData['uri'])) {
            // Determine uri type.
            if (filter_var($termData['uri'], FILTER_VALIDATE_URL) || preg_match(sprintf('/%s/', Constant::URN), $termData['uri'])) {
                return new Iri($termData['uri']);
            }
            else {
                throw new InvalidResultException(sprintf('Result "%s" is not a valid uri', $termData['uri']));
            }
        }
        throw new InvalidResultException(sprintf('Result type "%s" is not a valid type', key($termData)));
    }
}
