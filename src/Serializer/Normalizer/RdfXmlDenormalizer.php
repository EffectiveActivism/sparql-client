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

class RdfXmlDenormalizer implements DenormalizerInterface
{
    const TYPE = 'rdf+xml';

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @throws InvalidResultException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): array
    {
        return $this->recursiveParsr($data);
    }

    public function recursiveParsr(array $data): array
    {
        $sets = [];
        // Extract namespaces.
        foreach ($data as $key => $value) {
            $parts = explode(':', $key);
            if (count($parts) === 2) {
                if (
                    is_array($value) &&
                    isset($value['#'])
                ) {

                }
                list($prefix, $localPart) = $parts;
                $iri = new PrefixedIri($prefix, $localPart);
                $sets[] = [
                    'iri' => $iri,
                    'children' => $this->recursiveParsr($value),
                ];
            }
        }
        return $sets;
    }
}
