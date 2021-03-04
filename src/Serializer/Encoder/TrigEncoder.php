<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\EncoderInterface;

class TrigEncoder implements EncoderInterface
{
    const FORMAT = 'trig';

    public function encode($data, string $format, array $context = []): string
    {
        $uri = $data['uri'];
        $graph = $data['graph'];
        $type = $data['entityType'];
        $trig = "<$uri> rdf:typeof $type .\n";
        foreach ($graph as $predicate => $object) {
            // TODO: Determine object type.
            $trig .= "<$uri> $predicate \"$object\" .\n";
        }
        $trig = "<$uri> {\n" . $trig . "}\n";
        // TODO: Use namespaces from configuration.
        $trig = "@prefix schema: <http://schema.org/> .\n" . $trig;
        $trig = "@prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#> .\n" . $trig;
        return $trig;
    }

    public function supportsEncoding(string $format): bool
    {
        return self::FORMAT === $format;
    }
}
