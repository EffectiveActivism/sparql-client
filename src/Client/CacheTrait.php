<?php declare(strict_types=1);

namespace EffectiveActivism\SparQlClient\Client;

use EffectiveActivism\SparQlClient\Constant;
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

    protected function extractTags(array $patterns, array $tags = []): array
    {
        /** @var PatternInterface $pattern */
        foreach ($patterns as $pattern) {
            if (
                $pattern instanceof AbstractIri ||
                $pattern instanceof AbstractLiteral
            ) {
                $tags[] = $this->getKey($pattern->serialize());
            }
            elseif ($pattern instanceof TripleInterface) {
                $tags = $this->extractTags([$pattern->getSubject(), $pattern->getObject()], $tags);
            }
            elseif ($pattern instanceof PatternInterface) {
                $tags = $this->extractTags($pattern->toArray(), $tags);
            }
        }
        return $tags;
    }
}
