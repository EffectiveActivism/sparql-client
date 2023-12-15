<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use EffectiveActivism\SparQlClient\Exception\InvalidResultException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SparQlAskDenormalizer implements DenormalizerInterface
{
    const TYPE = 'sparql-ask';

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === self::TYPE;
    }

    /**
     * @throws InvalidResultException
     */
    public function denormalize($data, string $type, string $format = null, array $context = []): bool
    {
        if (isset($data['boolean'])) {
            return $data['boolean'] === 'true';
        }
        throw new InvalidResultException('ASK query did not give expected response');
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            self::TYPE => true,
        ];
    }
}
