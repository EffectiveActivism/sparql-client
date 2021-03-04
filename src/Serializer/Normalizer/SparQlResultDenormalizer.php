<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Normalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class SparQlResultDenormalizer implements DenormalizerInterface
{
    public function denormalize($data, string $type, string $format = null, array $context = []): array
    {
        $graph = [];
        if (
            isset($data['results']['result']['binding']['@name']) ||
            isset($data['results']['result']['binding']['@property'])
        ) {
            list($predicate, $object) = $this->extractTriple($data['results']['result']['binding']);
            if ($predicate !== null && $object !== null) {
                $graph[$predicate][] = $object;
            }
        }
        elseif (isset($data['results']['result']['binding'])) {
            foreach ($data['results']['result']['binding'] as $result) {
                list($predicate, $object) = $this->extractTriple($result);
                if ($predicate !== null && $object !== null) {
                    $graph[$predicate][] = $object;
                }
            }
        }
        elseif (isset($data['results']['result'])) {
            foreach ($data['results']['result'] as $result) {
                // Check if multiple variables.
                if (is_int(key($result['binding']))) {
                    foreach ($result['binding'] as $triple) {
                        list($predicate, $object) = $this->extractTriple($triple);
                        if ($predicate !== null && $object !== null) {
                            $graph[$predicate][] = $object;
                        }
                    }
                }
                else {
                    list($predicate, $object) = $this->extractTriple($result['binding']);
                    if ($predicate !== null && $object !== null) {
                        $graph[$predicate][] = $object;
                    }
                }
            }
        }
        return $graph;
    }

    public function supportsDenormalization($data, string $type, string $format = null): bool
    {
        return $type === 'rdf';
    }

    private function extractTriple(array $triple): array
    {
        $bindingName = null;
        foreach ($triple as $key => $value) {
            if (str_starts_with($key, '@')) {
                $bindingName = $key;
            }
        }
        if (isset($bindingName)) {
            if (isset($triple['uri'])) {
                return [$triple[$bindingName], $triple['uri']];
            }
            elseif (isset($triple['literal'])) {
                return [$triple[$bindingName], $triple['literal']];
            }
            else {
                return [$triple[$bindingName], null];
            }
        }
        else {
            return [null, null];
        }
    }
}
