<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SparQlConstructDenormalizer implements DenormalizerInterface
{
    const TYPE = 'sparql-construct';

    public function supportsDenormalization($data, string $type, string $format = null, array $context = []): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @throws SparQlException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): array
    {
        $sets = [];
        if (isset($data['rdf:Description']) && is_array($data['rdf:Description'])) {
            if (isset($data['rdf:Description']['@rdf:about'])) {
                $sets = array_merge($sets, $this->getTerms($data['rdf:Description']));
            }
            elseif (isset($data['rdf:Description'][0]['@rdf:about'])) {
                foreach ($data['rdf:Description'] as $result) {
                    $sets = array_merge($sets, $this->getTerms($result));
                }
            }
        }
        return $sets;
    }

    /**
     * @throws SparQlException
     */
    protected function getTerms(array $set): array
    {
        $terms = [];
        $subject = new Iri($set['@rdf:about']);
        unset($set['@rdf:about']);
        foreach ($set as $type => $value) {
            $triple = [];
            list($prefix, $localPart) = explode(':', $type);
            $predicate = new PrefixedIri($prefix, $localPart);
            if (is_string($value)) {
                $triple[] = $subject;
                $triple[] = $predicate;
                $triple[] = new PlainLiteral($value);
            }
            if (
                is_array($value) &&
                isset($value['@rdf:resource'])
            ) {
                $triple[] = $subject;
                $triple[] = $predicate;
                $triple[] = new Iri($value['@rdf:resource']);
            }
            $terms[] = $triple;
        }
        return $terms;
    }

    public function getSupportedTypes(?string $format = null): array
    {
        return [
            self::TYPE => true,
        ];
    }
}
