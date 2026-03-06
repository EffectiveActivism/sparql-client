<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Exception\SparQlException;
use EffectiveActivism\SparQlClient\Syntax\Term\BlankNode\BlankNode;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\Iri;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\PrefixedIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\PlainLiteral;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\TypedLiteral;
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
        $defaultSchema = $data['@xmlns:schema'] ?? '';
        if (isset($data['rdf:Description']) && is_array($data['rdf:Description'])) {
            if (isset($data['rdf:Description']['@rdf:about']) || isset($data['rdf:Description']['@rdf:nodeID'])) {
                $sets = array_merge($sets, $this->getTerms($data['rdf:Description'], $defaultSchema));
            }
            elseif (isset($data['rdf:Description'][0])) {
                foreach ($data['rdf:Description'] as $result) {
                    $sets = array_merge($sets, $this->getTerms($result, $defaultSchema));
                }
            }
        }
        return $sets;
    }

    /**
     * @throws SparQlException
     */
    protected function getTerms(array $set, string $defaultSchema): array
    {
        $terms = [];
        if (isset($set['@rdf:about'])) {
            $subject = new Iri($set['@rdf:about']);
            unset($set['@rdf:about']);
        }
        else {
            $subject = new BlankNode($set['@rdf:nodeID']);
            unset($set['@rdf:nodeID']);
        }
        foreach ($set as $type => $values) {
            if (str_contains($type, ':')) {
                list($prefix, $localPart) = explode(':', $type);
                $predicate = new PrefixedIri($prefix, $localPart);
            }
            else {
                $predicate = new Iri($defaultSchema . $type);
            }
            // A repeated predicate is decoded by XmlEncoder as a numeric array of value arrays.
            if (is_array($values) && array_key_exists(0, $values)) {
                $valueList = $values;
            }
            else {
                $valueList = [$values];
            }
            foreach ($valueList as $value) {
                if (is_string($value)) {
                    $terms[] = [$subject, $predicate, new PlainLiteral($value)];
                }
                elseif (is_array($value) && isset($value['@rdf:resource'])) {
                    $terms[] = [$subject, $predicate, new Iri($value['@rdf:resource'])];
                }
                elseif (is_array($value) && isset($value['@rdf:datatype'])) {
                    $dataType = $value['@rdf:datatype'];
                    $literalValue = $value['#'] ?? '';
                    if (filter_var($dataType, FILTER_VALIDATE_URL)) {
                        $dataTypeIri = new Iri($dataType);
                    }
                    else {
                        list($dtPrefix, $dtLocal) = explode(':', $dataType, 2);
                        $dataTypeIri = new PrefixedIri($dtPrefix, $dtLocal);
                    }
                    $terms[] = [$subject, $predicate, new TypedLiteral($literalValue, $dataTypeIri)];
                }
                elseif (is_array($value) && isset($value['@rdf:nodeID'])) {
                    $terms[] = [$subject, $predicate, new BlankNode($value['@rdf:nodeID'])];
                }
            }
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
