<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Exception\InvalidResultException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\TermInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\TypedLiteral;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SparQlResultDenormalizer implements DenormalizerInterface
{
    const TYPE = 'sparql-result';

    /**
     * @throws InvalidResultException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): array
    {
        $set = [];
        if (isset($data['results'])) {
            foreach ($data['results'] as $result) {
                if (key($result) === 'binding') {
                    $set = $this->getTerms($result[key($result)]);
                }
                elseif (isset($result[0]) && is_array($result[0]) && key($result[0]) === 'binding') {
                    foreach ($result as $binding) {
                        $set = array_merge($set, $this->getTerms($binding['binding']));
                    }
                }
            }
        }
        return $set;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @throws InvalidResultException
     */
    protected function getTerms(array $binding): array
    {
        $terms = [];
        if (isset($binding['@name'])) {
            $terms[] = $this->getTerm($binding);
        }
        elseif (isset($binding[0]['@name'])) {
            foreach ($binding as $termData) {
                $terms[] = $this->getTerm($termData);
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
            } elseif (is_array($termData['literal']) && isset($termData['literal']['datatype'])) {
                $dataType = $termData['literal']['datatype'];
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
            elseif (count(explode(':', $termData['uri'])) === 2) {
                list($prefix, $localPart) = explode(':', $termData['uri']);
                return new PrefixedIri($prefix, $localPart);
            }
            else {
                throw new InvalidResultException(sprintf('Result "%s" is not a valid uri', $termData['uri']));
            }
        }
        throw new InvalidResultException(sprintf('Result type "%s" is not a valid type', key($termData)));
    }
}
