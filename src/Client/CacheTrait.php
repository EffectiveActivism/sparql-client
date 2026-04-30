<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Graph\GraphInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\PatternInterface;
use EffectiveActivism\SparQlClient\Syntax\Pattern\Triple\TripleInterface;
use EffectiveActivism\SparQlClient\Syntax\Term\Iri\AbstractIri;
use EffectiveActivism\SparQlClient\Syntax\Term\Literal\AbstractLiteral;
use Ramsey\Uuid\Uuid;

trait CacheTrait
{
    protected function getKey(string $value): string
    {
        return Uuid::uuid5(Constant::NAMESPACE_CACHE, $value)->toString();
    }

    /**
     * Extracts cache tags from patterns. The accumulator is keyed by the
     * serialized term so a given IRI/literal yields at most one UUID5 call
     * per call chain — keeping per-row tagging tractable when SELECT/CONSTRUCT
     * results contain repeated values (predicates, type IRIs, common objects).
     * Callers iterate values, so the key shape is internal.
     */
    protected function extractTags(array $patterns, array $tags = []): array
    {
        /** @var PatternInterface $pattern */
        foreach ($patterns as $pattern) {
            if (
                $pattern instanceof AbstractIri ||
                $pattern instanceof AbstractLiteral
            ) {
                $serialized = $pattern->serialize();
                if (!isset($tags[$serialized])) {
                    $tags[$serialized] = $this->getKey($serialized);
                }
            }
            elseif ($pattern instanceof TripleInterface) {
                $tags = $this->extractTags([$pattern->getSubject(), $pattern->getObject()], $tags);
            }
            elseif ($pattern instanceof GraphInterface) {
                $tags = $this->extractTags(array_merge([$pattern->getGraph()], $pattern->toArray()), $tags);
            }
            elseif ($pattern instanceof PatternInterface) {
                $tags = $this->extractTags($pattern->toArray(), $tags);
            }
        }
        return $tags;
    }
}
